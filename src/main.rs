pub mod models;
pub mod schema;

use std::env;

use actix_cors::Cors;
use actix_web::{http::header, middleware, web::{self, Data}, App, Error, HttpRequest, HttpResponse, HttpServer, Responder};
use dotenvy::dotenv;
use juniper::{graphql_object, EmptyMutation, EmptySubscription, FieldResult, RootNode};
use diesel::{r2d2, PgConnection};
use juniper_actix::{graphiql_handler, graphql_handler};
use models::Cat;
use diesel::prelude::*;
use diesel_migrations::{embed_migrations, EmbeddedMigrations, MigrationHarness};
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

struct Query;

#[graphql_object]
#[graphql(context = Context)]
impl Query {
    fn cat(
        nickname: String,
        context: &Context,
    ) -> FieldResult<Cat> {
        use self::schema::cats::dsl::*;
        let conn = &mut context.db.get().unwrap();
        let result_cat = cats
            .filter(name.eq(nickname))
            .select(Cat::as_select())
            .load(conn)
            .expect("Error loading cats");
        // Return the result.
        Ok(result_cat.get(0).unwrap().clone())
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
