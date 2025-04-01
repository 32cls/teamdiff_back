
use diesel::prelude::*;
use juniper::GraphQLObject;

use crate::models::{Account, Summoner};

#[derive(GraphQLObject)]
#[graphql(description = "A summoner as defined by Riot API")]
pub struct GqlSummoner {
    #[graphql(desc = "Identifier of the summoner")]
    pub id: String,
    #[graphql(desc = "Icon of the summoner")]
    pub icon: i32,
    #[graphql(desc = "Experience level of the summoner")]
    pub level: i32
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
    pub fn from_db(account: &Account, conn: &PgConnection) -> Self {
        let summoner = Summoner::belonging_to(&account)
            .first::<Summoner>(&mut conn)
            .ok()
            .map(|s| GqlSummoner {
                id: s.id,
                icon: s.icon,
                level: s.level,
            });

        GqlAccount {
            name: account.name.clone(),
            tag: account.tag.clone(),
            summoner,
        }
    }
}