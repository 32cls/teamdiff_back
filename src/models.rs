use diesel::prelude::*;
use chrono::NaiveDateTime;
use crate::schema::{accounts, summoners};

#[derive(Identifiable, Queryable, Insertable, Selectable, Clone, Debug, AsChangeset)]
#[diesel(table_name = accounts)]
#[diesel(primary_key(puuid))]
#[diesel(check_for_backend(diesel::pg::Pg))]
pub struct Account {
    pub puuid: String,
    pub name: String,
    pub tag: String
}

#[derive(Identifiable, Queryable, Insertable, Selectable, Associations, Clone, Debug, PartialEq, AsChangeset)]
#[diesel(table_name = summoners)]
#[diesel(belongs_to(Account, foreign_key = account_puuid))]
#[diesel(check_for_backend(diesel::pg::Pg))]
pub struct Summoner {
    pub id: String,
    pub icon: i32,
    pub level: i32,
    pub revision_date: NaiveDateTime,
    pub account_puuid: String
}

#[derive(Identifiable, Queryable, Insertable, Selectable, Associations, Clone, Debug, PartialEq, AsChangeset)]
#[diesel(table_name = matches)]
#[diesel(check_for_backend(diesel::pg::Pg))]
pub struct Match {
    pub id: i32,
    pub name: String,
}

#[derive(Identifiable, Queryable, Insertable, Selectable, Associations, Clone, Debug, PartialEq, AsChangeset)]
#[diesel(belongs_to(Match))]
#[diesel(belongs_to(Summoner))]
#[diesel(table_name = matches_summoners)]
#[diesel(primary_key(book_id, author_id))]
pub struct MatchSummoner {
    pub match_id: String,
    pub summoner_id: String,
}