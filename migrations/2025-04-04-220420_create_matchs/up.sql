-- Your SQL goes here
CREATE TABLE matches (
    id VARCHAR PRIMARY KEY,
    duration INTEGER NOT NULL
);

CREATE TABLE participants (
    match_id VARCHAR NOT NULL,
    summoner_id VARCHAR NOT NULL,
    FOREIGN KEY (match_id) REFERENCES matches(id),
    FOREIGN KEY (summoner_id) REFERENCES summoners(id),
    PRIMARY KEY (match_id, summoner_id)
);