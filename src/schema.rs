// @generated automatically by Diesel CLI.

diesel::table! {
    accounts (puuid) {
        puuid -> Varchar,
        name -> Varchar,
        tag -> Varchar,
    }
}

diesel::table! {
    matches (id) {
        id -> Varchar,
    }
}

diesel::table! {
    matches_summoners (match_id, summoner_id) {
        match_id -> Varchar,
        summoner_id -> Varchar,
    }
}

diesel::table! {
    summoners (id) {
        id -> Varchar,
        icon -> Int4,
        revision_date -> Timestamp,
        level -> Int4,
        account_puuid -> Varchar,
    }
}

diesel::joinable!(matches_summoners -> matches (match_id));
diesel::joinable!(matches_summoners -> summoners (summoner_id));
diesel::joinable!(summoners -> accounts (account_puuid));

diesel::allow_tables_to_appear_in_same_query!(
    accounts,
    matches,
    matches_summoners,
    summoners,
);
