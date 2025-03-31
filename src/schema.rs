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

diesel::allow_tables_to_appear_in_same_query!(
    accounts,
    cats,
);
