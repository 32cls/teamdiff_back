pub mod models;
pub mod schema;
pub mod graphql;
pub mod dto;

use std::env;
use actix_cors::Cors;
use actix_web::{http::header, middleware, rt::{task, Runtime}, web::{self, Data}, App, Error, HttpRequest, HttpResponse, HttpServer, Responder};
use dotenvy::dotenv;
use dto::AccountDto;
use graphql::GqlAccount;
use juniper::{graphql_object, EmptyMutation, EmptySubscription, FieldResult, RootNode};
use diesel::{r2d2, PgConnection};
use juniper_actix::{graphiql_handler, graphql_handler};
use models::Account;
use diesel::prelude::*;
use diesel_migrations::{embed_migrations, EmbeddedMigrations, MigrationHarness};
use reqwest::ClientBuilder;
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

async fn get_account_from_api(region: String, name: String, tag: String) -> Result<Account, Error> {
    let mut headers = reqwest::header::HeaderMap::new();
    headers.insert("X-Riot-Token", std::env::var("RIOT_API_KEY").expect("RIOT_API_KEY should be set").parse().unwrap());
    let client = ClientBuilder::new().default_headers(headers).build().unwrap();
    let account_dto = client
        .get(format!("https://{region}.api.riotgames.com/riot/account/v1/accounts/by-riot-id/{name}/{tag}"))
        .send()
        .await
        .map_err(|e| actix_web::error::ErrorInternalServerError(e))?
        .json::<AccountDto>()
        .await
        .map_err(|e| actix_web::error::ErrorInternalServerError(e))?;   
    Ok(Account { puuid: account_dto.puuid, name: account_dto.gamename, tag: account_dto.tagline })
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
        let conn = &mut context.db.get().unwrap();
        let result_acc: Vec<Account> = accounts
            .filter(name.eq(game_name.clone()).and(tag.eq(game_tag.clone())))
            .select(Account::as_select())
            .load(conn)
            .expect("Error loading account");

        if result_acc.is_empty() {
            let acc = task::spawn_blocking(move || {
                let resp = Runtime::new()
                    .unwrap()
                    .block_on(get_account_from_api(String::from("europe"), game_name, game_tag))
                    .unwrap();
                resp
            }).await.unwrap();
            println!("{:?}",acc);
            let exist_account = accounts.find(&acc.puuid).first::<Account>(conn);
            if exist_account.is_ok(){
                diesel::update(accounts)
                    .set((name.eq(&acc.name),tag.eq(&acc.tag)))
                    .filter(puuid.eq(&acc.puuid))
                    .execute(conn)
                    .expect("Error while saving account");
            } else {
                diesel::insert_into(accounts)
                    .values(acc.clone())
                    .execute(conn)
                    .expect("Error while saving account");
            }
            Ok(GqlAccount::from_db(&acc, conn))
        } else {
            Ok(GqlAccount::from_db(result_acc.get(0).unwrap(),conn))
        }
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
