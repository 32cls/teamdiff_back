// @generated automatically by Diesel CLI.

diesel::table! {
    accounts (puuid) {
        puuid -> Varchar,
        name -> Varchar,
        tag -> Varchar,
    }
}

diesel::table! {
    champions (id) {
        id -> Int4,
        name -> Varchar,
        icon -> Varchar,
    }
}

diesel::table! {
    matches (id) {
        id -> Varchar,
        duration -> Int4,
    }
}

diesel::table! {
    participants (match_id, summoner_id) {
        match_id -> Varchar,
        summoner_id -> Varchar,
        champion_id -> Int4,
        team_id -> Int4,
        team_position -> Varchar,
        win -> Bool,
        kills -> Int4,
        deaths -> Int4,
        assists -> Int4,
        level -> Int4,
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

diesel::joinable!(participants -> champions (champion_id));
diesel::joinable!(participants -> matches (match_id));
diesel::joinable!(participants -> summoners (summoner_id));
diesel::joinable!(summoners -> accounts (account_puuid));

diesel::allow_tables_to_appear_in_same_query!(
    accounts,
    champions,
    matches,
    participants,
    summoners,
);
