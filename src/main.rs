pub mod models;
pub mod schema;
pub mod graphql;
pub mod dto;

use std::env;
use actix_cors::Cors;
use actix_web::{http::header, middleware, rt::{task, Runtime}, web::{self, Data}, App, Error, HttpRequest, HttpResponse, HttpServer, Responder};
use chrono::DateTime;
use dotenvy::dotenv;
use dto::{AccountDto, MatchDto, SummonerDto};
use futures::future::try_join_all;
use graphql::{GqlAccount, GqlSummoner};
use juniper::{graphql_object, EmptyMutation, EmptySubscription, FieldResult, RootNode};
use diesel::{r2d2, PgConnection};
use juniper_actix::{graphiql_handler, graphql_handler};
use models::{Account, Match, Participant, Summoner};
use diesel::prelude::*;
use diesel_migrations::{embed_migrations, EmbeddedMigrations, MigrationHarness};
use reqwest::{Client, ClientBuilder};
pub const MIGRATIONS: EmbeddedMigrations = embed_migrations!("migrations");

type DbPool = r2d2::Pool<r2d2::ConnectionManager<PgConnection>>;
type DB = diesel::pg::Pg;

struct Context {
    db: DbPool,
}

impl juniper::Context for Context {}

fn initialize_db_pool() -> DbPool {
    let conn_spec = std::env::var("DATABASE_URL").expect("DATABASE_URL should be set");
    let manager = r2d2::ConnectionManager::<PgConnection>::new(conn_spec);
    r2d2::Pool::builder()
        .build(manager)
        .expect("Error connecting to the database")
}

fn client() -> Client {
    let mut headers = reqwest::header::HeaderMap::new();
    headers.insert("X-Riot-Token", std::env::var("RIOT_API_KEY").expect("RIOT_API_KEY should be set").parse().unwrap());
    ClientBuilder::new().default_headers(headers).build().unwrap()
}

async fn get_account_data(region: String, name: String, tag: String) -> Result<Account, Error> {
    
    let account_dto = client()
        .get(format!("https://{region}.api.riotgames.com/riot/account/v1/accounts/by-riot-id/{name}/{tag}"))
        .send()
        .await
        .map_err(|e| actix_web::error::ErrorInternalServerError(e))?
        .json::<AccountDto>()
        .await
        .map_err(|e| actix_web::error::ErrorInternalServerError(e))?;   
    Ok(Account { puuid: account_dto.puuid, name: account_dto.gamename, tag: account_dto.tagline })
}

async fn get_summoner_data(region: String, puuid: String) -> Result<Summoner, Error> {
    let summoner_dto = client()
        .get(format!("https://{region}.api.riotgames.com/lol/summoner/v4/summoners/by-puuid/{puuid}"))
        .send()
        .await
        .map_err(|e| actix_web::error::ErrorInternalServerError(e))?
        .json::<SummonerDto>()
        .await
        .map_err(|e| actix_web::error::ErrorInternalServerError(e))?;

    Ok(Summoner { 
        id: summoner_dto.id, 
        icon: summoner_dto.profile_icon_id, 
        level: summoner_dto.summoner_level, 
        revision_date: DateTime::from_timestamp_millis(summoner_dto.revision_date).expect("Invalid timestamp supplied").naive_utc(),
        account_puuid: puuid 
    })   

}

async fn get_matches_data(region: String, puuid: String) -> Result<Vec<Match>, Error> {
    let matches_id = client()
        .get(format!("https://{region}.api.riotgames.com/lol/match/v5/matches/by-puuid/{puuid}/ids"))
        .send()
        .await
        .map_err(|e| actix_web::error::ErrorInternalServerError(e))?
        .json::<Vec<String>>()
        .await
        .map_err(|e| actix_web::error::ErrorInternalServerError(e))?;
    
    let matches_futures = matches_id.into_iter().map(|match_id| {
    let value = region.clone();
    async move {
        client()
            .get(format!("https://{value}.api.riotgames.com/lol/match/v5/matches/{match_id}"))
            .send()
            .await
            .map_err(|e| actix_web::error::ErrorInternalServerError(e))?
            .json::<MatchDto>()
            .await
            .map_err(|e| actix_web::error::ErrorInternalServerError(e))
    }
    });

    let matches_data: Vec<MatchDto> = try_join_all(matches_futures).await?;
    let mapped_matchs = matches_data.into_iter().map(Match::from_dto).collect::<Vec<Match>>();
    Ok(mapped_matchs)   
}
struct Query;

#[graphql_object]
#[graphql(context = Context)]
impl Query {
    async fn account(
        game_name: String,
        game_tag: String,
        context: &Context,
    ) -> FieldResult<GqlAccount> {
        use self::schema::accounts::dsl::*;
        use self::schema::summoners::dsl::*;
        let connection = &mut context.db.get().unwrap();
        let gql_acc: GqlAccount = match accounts
            .left_join(summoners)
            .filter(name.eq(game_name.clone()).and(tag.eq(game_tag.clone())))
            .select((Account::as_select(), Option::<Summoner>::as_select()))
            .first::<(Account, Option<Summoner>)>(connection) {
                Ok((acc_res, sum_res)) => {
                    GqlAccount::from_obj(&acc_res, sum_res.as_ref().map(GqlSummoner::from_obj))
                }
                Err(_) => {
                    let (account, summoner) = task::spawn_blocking(move || {
                        let acc_resp = Runtime::new()
                            .unwrap()
                            .block_on(get_account_data(String::from("europe"), game_name, game_tag))
                            .unwrap();
                        let sum_resp = Runtime::new()
                            .unwrap()
                            .block_on(get_summoner_data(String::from("EUW1"), acc_resp.puuid.clone()));

                        (acc_resp, sum_resp.ok()) 
                    }).await.unwrap();
                    diesel::insert_into(accounts).values(&account).on_conflict(puuid).do_update().set(&account).execute(connection)?;
                    match summoner {
                        Some(summ) => {
                            diesel::insert_into(summoners).values(&summ).execute(connection)?;
                            let puuid_str = summ.account_puuid.clone();
                            let summoner_matches = task::spawn_blocking(move || {
                                let sum_mat = Runtime::new()
                                    .unwrap()
                                    .block_on(get_matches_data(String::from("EUW1"), puuid_str))
                                    .unwrap();
                                sum_mat
                            }).await.unwrap();
                            diesel::insert_into(schema::matches::table).values(&summoner_matches).execute(connection)?;
                            diesel::insert_into(schema::participants::table).values(&summoner_matches.iter().map(|m| Participant { match_id: m.id.clone(), summoner_id: summ.id.clone() }).collect::<Vec<Participant>>()).execute(connection)?;
                            GqlAccount::from_obj(&account, Some(GqlSummoner::from_obj(&summ)))
                        }
                        None => {
                            GqlAccount::from_obj(&account, None)
                        }
                    }
                }
            };
            Ok(gql_acc)
        }        
}

type Schema = RootNode<'static, Query, EmptyMutation<Context>, EmptySubscription<Context>>;

fn schema() -> Schema {
    Schema::new(Query, EmptyMutation::<Context>::new(), EmptySubscription::<Context>::new())
}

async fn graphiql() -> Result<HttpResponse, Error> {
    graphiql_handler("/graphql", Some("/subscriptions")).await
}

async fn graphql(
    req: HttpRequest,
    payload: web::Payload,
    schema: Data<Schema>,
) -> Result<HttpResponse, Error> {
    let ctx = Context {db: initialize_db_pool()};
    graphql_handler(&schema, &ctx, req, payload).await
}

async fn homepage() -> impl Responder {
    HttpResponse::Ok()
        .insert_header(("content-type", "text/html"))
        .message_body(
            "<html><h1>juniper_actix/subscription example</h1>\
                   <div>visit <a href=\"/graphiql\">GraphiQL</a></div>\
             </html>",
        )
}

fn run_migrations(connection: &mut impl MigrationHarness<DB>) -> Result<(), Box<dyn std::error::Error + Send + Sync + 'static>> {
    connection.run_pending_migrations(MIGRATIONS)?;
    Ok(())
}

#[actix_web::main]
async fn main() -> std::io::Result<()> {
    dotenv().ok(); 
    env::set_var("RUST_LOG", "info");
    env_logger::init();
    let ctx = initialize_db_pool();
    let _ = run_migrations(&mut ctx.get().unwrap());

    HttpServer::new(move || {
        App::new()
            .app_data(Data::new(schema()))
            .wrap(
                Cors::default()
                    .allow_any_origin()
                    .allowed_methods(vec!["POST", "GET"])
                    .allowed_headers(vec![header::AUTHORIZATION, header::ACCEPT])
                    .allowed_header(header::CONTENT_TYPE)
                    .supports_credentials()
                    .max_age(3600),
            )
            .wrap(middleware::Compress::default())
            .wrap(middleware::Logger::default())
            //.service(web::resource("/subscriptions").route(web::get().to(subscriptions)))
            .service(
                web::resource("/graphql")
                    .route(web::post().to(graphql))
                    .route(web::get().to(graphql)),
            )
            .service(web::resource("/graphiql").route(web::get().to(graphiql)))
            .default_service(web::to(homepage))
    })
    .bind("0.0.0.0:8080")?
    .run()
    .await
}
