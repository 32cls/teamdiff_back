-- Your SQL goes here
CREATE TABLE summoners(
    id VARCHAR NOT NULL PRIMARY KEY,
    icon INTEGER NOT NULL,
    revision_date TIMESTAMP NOT NULL,
    level INTEGER NOT NULL,
    account_puuid VARCHAR UNIQUE NOT NULL REFERENCES accounts(puuid)
)