use diesel::prelude::*;
use juniper::GraphQLObject;
use serde::Deserialize;

#[derive(GraphQLObject, Queryable, Insertable, Selectable, Clone, Debug)]
#[diesel(table_name = crate::schema::accounts)]
#[diesel(check_for_backend(diesel::pg::Pg))]
#[graphql(description = "An account as defined by Riot API")]
pub struct Account {
    #[graphql(ignore)]
    pub puuid: String,
    #[graphql(desc = "Name of the account of the player")]
    pub name: String,
    #[graphql(desc = "Tag (preceded by '#') of the account of the player")]
    pub tag: String
}

#[derive(Deserialize, Debug)]
pub struct AccountDto {
    pub puuid: String,
    #[serde(rename = "gameName")]
    pub gamename: String,
    #[serde(rename = "tagLine")]
    pub tagline: String
}

#[derive(GraphQLObject, Queryable, Insertable, Selectable, Associations, Clone, Debug, PartialEq)]
#[diesel(table_name = crate::schema::summoners)]
#[diesel(belongs_to(Account, foreign_key = puuid))]
#[diesel(check_for_backend(diesel::pg::Pg))]
#[graphql(description = "A summoner as defined by Riot API")]
pub struct Summoner {
    #[graphql(desc = "Identifier of the summoner")]
    pub id: String,
    #[graphql(ignore)]
    pub puuid: String,
    #[graphql(desc = "Identifier of the profile icon of the summoner")]
    pub icon: i32,
    #[graphql(desc = "Experience level of the summoner")]
    pub level: i32
}


#[derive(Deserialize, Debug)]
pub struct SummonerDto {
    #[serde(rename = "profileIconId")]
    pub profile_icon_id: i32,
    pub id: String,
    #[serde(rename = "summonerLevel")]
    pub summoner_level: i32
}
