use diesel::prelude::*;
use juniper::GraphQLObject;
use serde::Deserialize;

#[derive(GraphQLObject, Queryable, Selectable, Clone)]
#[diesel(table_name = crate::schema::cats)]
#[diesel(check_for_backend(diesel::pg::Pg))]
#[graphql(description = "A very friendly animal")]
pub struct Cat {
    name: String,
    color: String
}

#[derive(GraphQLObject, Queryable, Insertable, Selectable, Clone, Deserialize)]
#[diesel(table_name = crate::schema::accounts)]
#[diesel(check_for_backend(diesel::pg::Pg))]
#[graphql(description = "An account as defined by Riot API")]
pub struct Account {
    pub puuid: String,
    pub name: String,
    pub tag: String
}
