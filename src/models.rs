use diesel::prelude::*;
use juniper::GraphQLObject;

#[derive(GraphQLObject, Queryable, Selectable, Clone)]
#[diesel(table_name = crate::schema::cats)]
#[diesel(check_for_backend(diesel::pg::Pg))]
#[graphql(description = "A very friendly animal")]
pub struct Cat {
    name: String,
    color: String
}
