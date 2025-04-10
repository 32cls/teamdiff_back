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
use juniper::{graphql_object, graphql_value, EmptySubscription, FieldError, FieldResult, RootNode};
use diesel::{r2d2, PgConnection};
use juniper_actix::{graphiql_handler, graphql_handler};
use models::{Account, Match, Participant, Summoner};
use diesel::prelude::*;
use diesel_migrations::{embed_migrations, EmbeddedMigrations, MigrationHarness};
use reqwest::{Client, ClientBuilder};
use schema::{matches, participants};
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

async fn get_matches_data(region: String, puuid: String) -> Result<(Vec<Match>,Vec<Participant>), Error> {
    let matches_id = client()
        .get(format!("https://{region}.api.riotgames.com/lol/match/v5/matches/by-puuid/{puuid}/ids?queue=420&count=10"))
        .send()
        .await
        .map_err(|e| actix_web::error::ErrorInternalServerError(e))?
        .json::<Vec<String>>()
        .await
        .map_err(|e| actix_web::error::ErrorInternalServerError(e))?;

    println!("Matches ID: {:?}", matches_id);
    
    let matches_futures = matches_id.into_iter().map(|match_id| {
        let cloned_region = region.clone();
        async move {
            let resp = client()
            .get(format!("https://{cloned_region}.api.riotgames.com/lol/match/v5/matches/{match_id}"))
            .send()
            .await
            .map_err(|e| actix_web::error::ErrorInternalServerError(e))?;

            let status = resp.status();
            let text = resp.text().await.map_err(|e| actix_web::error::ErrorInternalServerError(e))?;

            println!("🔍 Match {match_id} response (status {status}):\n{text}");

            if !status.is_success() {
                return Err(actix_web::error::ErrorInternalServerError(format!("API error: {}", text)));
            }

            // maintenant tu peux parser après le log
            let parsed = serde_json::from_str::<MatchDto>(&text)
                .map_err(|e| {
                    eprintln!("❌ Failed to parse MatchDto: {e}\nText: {text}");
                    actix_web::error::ErrorInternalServerError(e)
                })?;
            
            Ok(parsed)
        }
    });

    let matches_data: Vec<MatchDto> = try_join_all(matches_futures).await?;
    println!("Matches Data: {:?}", matches_data);
    let mapped_matchs = matches_data.iter().map(|match_dto: &MatchDto| Match::from_dto(match_dto)).collect::<Vec<Match>>();
    let mapped_participants = matches_data.iter().flat_map(|m| {
        m.info.participants.iter().map(|participant| {
            Participant::from_dto(m.metadata.match_id.clone(), participant.puuid.clone(), participant)
        }).collect::<Vec<Participant>>()
    }).collect::<Vec<Participant>>();
    Ok((mapped_matchs, mapped_participants))   
}
struct Query;
struct Mutation;

#[graphql_object]
#[graphql(context = Context)]
impl Query {
    async fn fetch_account(
        game_name: String,
        game_tag: String,
        context: &Context,
    ) -> FieldResult<GqlAccount> {
        use self::schema::accounts::dsl::*;
        use self::schema::summoners::dsl::*;
        let connection = &mut context.db.get().unwrap();
        let gql_acc: Option<GqlAccount> = match accounts
            .left_join(summoners)
            .filter(name.eq(game_name.clone()).and(tag.eq(game_tag.clone())))
            .select((Account::as_select(), Option::<Summoner>::as_select()))
            .first::<(Account, Option<Summoner>)>(connection) {
                Ok((acc_res, sum_res)) => {
                    match sum_res {
                        Some(summ) => {
                            let rows: Vec<(Match, Participant)> = Participant::belonging_to(&summ)
                                .inner_join(matches::table.on(matches::id.eq(participants::match_id)))
                                .order_by(matches::game_creation.desc())
                                .limit(10)
                                .select((Match::as_select(), Participant::as_select()))
                                .load::<(Match, Participant)>(connection)?;


                            let (matches_vec, participants_grouped): (Vec<Match>, Vec<Participant>) = rows.into_iter().unzip();
                            let grouped_participants = participants_grouped.grouped_by(&matches_vec);
                            
                            let gql_sum = GqlSummoner::from_obj(&summ, Some(matches_vec), Some(grouped_participants));
                            Some(GqlAccount::from_obj(&acc_res, Some(gql_sum)))
                        }
                        None => {
                            Some(GqlAccount::from_obj(&acc_res, None))
                        }
                    }
                }
                Err(_) => {
                    println!("Summoner not found");
                    None
                }
            };
            Ok(gql_acc.ok_or_else(|| FieldError::new("Summoner not found", graphql_value!({ "not_found": "Summoner not found" })))?)
        }        
}

#[graphql_object]
#[graphql(context = Context)]
impl Mutation {
    async fn refresh_account(
        game_name: String,
        game_tag: String,
        context: &Context,
    ) -> FieldResult<GqlAccount>{
        use self::schema::accounts::dsl::*;
        use self::schema::summoners::dsl::*;
        use self::schema::matches::dsl::*;
        use self::schema::participants::dsl::*;
        
        let connection = &mut context.db.get().unwrap();
        let account = task::spawn_blocking(move || {
            let acc_resp = Runtime::new()
                .unwrap()
                .block_on(get_account_data(String::from("europe"), game_name, game_tag))
                .unwrap();
            
            acc_resp
        }).await.unwrap();

        let account_id = account.puuid.clone();

        let summoner = task::spawn_blocking({
                    let account_id = account_id.clone();
                    move || {
                        let sum_resp = Runtime::new()
                            .unwrap()
                            .block_on(get_summoner_data(String::from("euw1"), account_id))
                            .unwrap();
                        
                        sum_resp
                    }
                }).await.unwrap();
        
        let (matches_vec, participants_vec) = task::spawn_blocking({
                    let account_id = account_id.clone();
                    move || {
                        let matches_resp = Runtime::new()
                            .unwrap()
                            .block_on(get_matches_data(String::from("euw1"), account_id))
                            .unwrap();
                        
                        matches_resp
                    }
                }).await.unwrap();        
        
        diesel::insert_into(accounts)
            .values(&account)
            .on_conflict(puuid)
            .do_update()
            .set((name.eq(account.name.clone()), tag.eq(account.tag.clone())))
            .execute(connection)?;

        diesel::insert_into(summoners)
            .values(&summoner)
            .on_conflict(schema::summoners::dsl::id)
            .do_nothing()
            .execute(connection)?;

        diesel::insert_into(matches)
            .values(&matches_vec)
            .on_conflict(schema::matches::dsl::id)
            .do_nothing()
            .execute(connection)?;

        diesel::insert_into(participants)
            .values(&participants_vec)
            .on_conflict((match_id, summoner_id))
            .do_nothing()
            .execute(connection)?;

        Ok(GqlAccount::from_obj(&account, Some(GqlSummoner::from_obj(&summoner, Some(matches_vec), Some(participants_vec)))))
    }
}
type Schema = RootNode<'static, Query, Mutation, EmptySubscription<Context>>;

fn schema() -> Schema {
    Schema::new(Query, Mutation, EmptySubscription::<Context>::new())
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
