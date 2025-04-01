use diesel::prelude::*;
use crate::schema::{accounts, summoners};

#[derive(Queryable, Insertable, Selectable, Clone, Debug)]
#[diesel(table_name = accounts)]
#[diesel(primary_key(puuid))]
#[diesel(check_for_backend(diesel::pg::Pg))]
pub struct Account {
    pub puuid: String,
    pub name: String,
    pub tag: String
}

#[derive(Queryable, Insertable, Selectable, Associations, Clone, Debug, PartialEq)]
#[diesel(table_name = summoners)]
#[diesel(belongs_to(Account, foreign_key = account_puuid))]
#[diesel(check_for_backend(diesel::pg::Pg))]
pub struct Summoner {
    pub id: String,
    pub icon: i32,
    pub level: i32,
    pub account_puuid: String
}
