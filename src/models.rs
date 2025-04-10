use diesel::prelude::*;
use chrono::NaiveDateTime;
use crate::{dto::{MatchDto, ParticipantDto}, schema::{accounts, matches, participants, summoners}};

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

#[derive(Identifiable, Queryable, Insertable, Selectable, Clone, Debug, PartialEq)]
#[diesel(table_name = matches)]
#[diesel(check_for_backend(diesel::pg::Pg))]
pub struct Match {
    pub id: String,
    pub duration: i32,
    pub game_creation: NaiveDateTime,
}

impl Match {
    pub fn from_dto(match_dto: &MatchDto) -> Self {
        Match { 
            id: match_dto.metadata.match_id.clone(),
            duration: match_dto.info.game_duration as i32,
            game_creation: NaiveDateTime::from_timestamp_millis(match_dto.info.game_creation).unwrap(), 
        }
    }
}

#[derive(Identifiable, Queryable, Insertable, Selectable, Associations, Clone, Debug, PartialEq)]
#[diesel(belongs_to(Match))]
#[diesel(belongs_to(Summoner))]
#[diesel(table_name = participants)]
#[diesel(primary_key(match_id, summoner_id))]
pub struct Participant {
    pub match_id: String,
    pub summoner_id: String,
    pub champion_id: i32,
    pub team_id: i32,
    pub win: bool,
    pub kills: i32,
    pub deaths: i32,
    pub assists: i32,
    pub level: i32,
    pub team_position: String,
}

impl Participant {
    pub fn from_dto(match_id: String, summoner_id: String, participant_dto: &ParticipantDto) -> Self {
        Participant {
            match_id: match_id,
            summoner_id: summoner_id,
            assists: participant_dto.assists,
            champion_id: participant_dto.champion_id,
            deaths: participant_dto.deaths,
            kills: participant_dto.kills,
            level: participant_dto.champ_level,
            team_id: participant_dto.team_id,
            team_position: participant_dto.team_position.clone(),
            win: participant_dto.win,
        }
    }
}
