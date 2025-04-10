
use chrono::Utc;
use juniper::{integrations::chrono::DateTime, GraphQLObject};
use crate::models::{Account, Match, Participant, Summoner};

#[derive(GraphQLObject, Debug)]
#[graphql(description = "A match played by the summoner")]
pub struct GqlMatch {
    #[graphql(desc = "Identifier of the match")]
    pub id: String,
    #[graphql(desc = "Duration of the match")]
    pub duration: i32,
    #[graphql(desc = "Datetime (UTC) at which the match was created")]
    pub game_creation: DateTime<Utc>,
    #[graphql(desc = "List of participants in the match")]
    pub participants: Vec<GqlParticipant>,
}

impl GqlMatch {
    pub fn from_obj(match_obj: Match, participants_obj: Vec<Participant>) -> Self {
        GqlMatch { 
            id: match_obj.id, 
            duration: match_obj.duration,
            game_creation: match_obj.game_creation.and_utc(),
            participants: participants_obj.into_iter().map(|participant| {
                GqlParticipant { 
                    id: participant.summoner_id.clone(), 
                    champion_id: participant.champion_id, 
                    team_id: participant.team_id, 
                    win: participant.win, 
                    kills: participant.kills, 
                    deaths: participant.deaths, 
                    assists: participant.assists, 
                    level: participant.level,
                }
            }).collect()
        }
    }
}

#[derive(GraphQLObject, Debug)]
#[graphql(description = "The summoner that played in the match")]
pub struct GqlParticipant {
    #[graphql(desc = "Identifier of the summoner")]
    pub id: String,
    #[graphql(desc = "Identifier of the champion played by the summoner")]
    pub champion_id: i32,
    #[graphql(desc = "Team ID of the summoner")]
    pub team_id: i32,
    #[graphql(desc = "Whether the summoner won the match")]
    pub win: bool,
    #[graphql(desc = "Number of kills made by the summoner")]
    pub kills: i32,
    #[graphql(desc = "Number of deaths made by the summoner")]
    pub deaths: i32,
    #[graphql(desc = "Number of assists made by the summoner")]
    pub assists: i32,
    #[graphql(desc = "Level of the champion played by the summoner")]
    pub level: i32,
}

impl GqlParticipant {
    pub fn from_batch(participants: &Vec<Participant>) -> Vec<GqlParticipant> {
        participants.into_iter().map(|participant| {
            GqlParticipant { 
                id: participant.summoner_id.clone(), 
                champion_id: participant.champion_id, 
                team_id: participant.team_id, 
                win: participant.win, 
                kills: participant.kills, 
                deaths: participant.deaths, 
                assists: participant.assists, 
                level: participant.level,
            }
        }).collect()        
    }
}

#[derive(GraphQLObject, Debug)]
#[graphql(description = "A summoner as defined by Riot API")]
pub struct GqlSummoner {
    #[graphql(desc = "Identifier of the summoner")]
    pub id: String,
    #[graphql(desc = "Icon id of the summoner")]
    pub icon: i32,
    #[graphql(desc = "Experience level of the summoner")]
    pub level: i32,
    #[graphql(desc = "Datetime (UTC) at which the summoner was last updated on Riot API")]
    pub revision: DateTime<Utc>,
    #[graphql(desc = "History of matches played by the summoner")]
    pub matches: Vec<GqlMatch>,
}

impl GqlSummoner {
    pub fn from_obj(summoner: &Summoner, history_opt: Option<Vec<Match>>, participants_opt: Option<Vec<Participant>>) -> Self {
        GqlSummoner { 
            id: summoner.id.clone(), 
            icon: summoner.icon,
            level: summoner.level,
            revision: summoner.revision_date.and_utc(),
            matches: history_opt.unwrap_or(Vec::new()).into_iter().map(|m| GqlMatch::from_obj(m, participants_opt.clone().unwrap_or(Vec::new()))).collect(),
        }
    }
}

#[derive(GraphQLObject)]
#[graphql(description = "An account as defined by Riot API")]
pub struct GqlAccount {
    #[graphql(desc = "Name of the account of the player")]
    pub name: String,
    #[graphql(desc = "Tag (usually preceded by '#') of the account of the player")]
    pub tag: String,
    #[graphql(desc = "Optional summoner associated with the account")]
    pub summoner: Option<GqlSummoner>
}

impl GqlAccount {
    pub fn from_obj(account: &Account, summoner: Option<GqlSummoner>) -> Self {
        GqlAccount { 
            name: account.name.clone(), 
            tag: account.tag.clone(), 
            summoner: summoner
        }
    }
}