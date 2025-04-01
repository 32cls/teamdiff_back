// @generated automatically by Diesel CLI.

diesel::table! {
    accounts (puuid) {
        puuid -> Varchar,
        name -> Varchar,
        tag -> Varchar,
    }
}

diesel::table! {
    cats (id) {
        id -> Int4,
        name -> Varchar,
        color -> Varchar,
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

diesel::joinable!(summoners -> accounts (account_puuid));

diesel::allow_tables_to_appear_in_same_query!(
    accounts,
    cats,
    summoners,
);
