
use chrono::Utc;
use diesel::prelude::*;
use juniper::{integrations::chrono::DateTime, GraphQLObject};
use url::Url;
use crate::models::{Account, Summoner};

#[derive(GraphQLObject, Debug)]
#[graphql(description = "A summoner as defined by Riot API")]
pub struct GqlSummoner {
    #[graphql(desc = "Identifier of the summoner")]
    pub id: String,
    #[graphql(desc = "Icon URL of the summoner")]
    pub icon: Url,
    #[graphql(desc = "Experience level of the summoner")]
    pub level: i32,
    #[graphql(desc = "Datetime (UTC) at which the summoner was last updated on Riot API")]
    pub revision: DateTime<Utc>
}

impl GqlSummoner {
    pub fn from_obj(summoner: &Summoner) -> Self {
        GqlSummoner { 
            id: summoner.id.clone(), 
            icon: Url::parse(&format!("https://ddragon.leagueoflegends.com/cdn/15.7.1/img/profileicon/{}.png",summoner.icon)).unwrap(),
            level: summoner.level,
            revision: summoner.revision_date.and_utc()
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
    pub fn from_db(account: &Account, conn: &mut PgConnection) -> Self {
        let summoner = Summoner::belonging_to(&account)
            .select(Summoner::as_select())
            .first(conn)
            .ok()
            .map(|s| GqlSummoner::from_obj(&s));
        println!("Summoner: {:?}", summoner);
        GqlAccount {
            name: account.name.clone(),
            tag: account.tag.clone(),
            summoner,
        }
    }

    pub fn from_obj(account: &Account, summoner: Option<Summoner>) -> Self {
        GqlAccount { 
            name: account.name.clone(), 
            tag: account.tag.clone(), 
            summoner: summoner.map(|s| GqlSummoner::from_obj(&s))
        }
    }
}