-- Your SQL goes here
CREATE TABLE summoners(
    id VARCHAR NOT NULL PRIMARY KEY,
    icon INTEGER NOT NULL,
    revision_date TIMESTAMP NOT NULL,
    level INTEGER NOT NULL,
    puuid VARCHAR NOT NULL REFERENCES accounts(puuid)
)