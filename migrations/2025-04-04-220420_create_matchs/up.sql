-- Your SQL goes here
CREATE TABLE matches (
    id VARCHAR PRIMARY KEY,
    duration INTEGER NOT NULL
);

CREATE TABLE champions (
    id INTEGER PRIMARY KEY,
    name VARCHAR NOT NULL,
    icon VARCHAR NOT NULL
);

CREATE TABLE participants (
    match_id VARCHAR NOT NULL,
    summoner_id VARCHAR NOT NULL,
    champion_id INTEGER NOT NULL,
    team_id INTEGER NOT NULL,
    team_position VARCHAR NOT NULL,
    win BOOLEAN NOT NULL,
    kills INTEGER NOT NULL,
    deaths INTEGER NOT NULL,
    assists INTEGER NOT NULL,
    level INTEGER NOT NULL,
    FOREIGN KEY (match_id) REFERENCES matches(id),
    FOREIGN KEY (summoner_id) REFERENCES summoners(id),
    FOREIGN KEY (champion_id) REFERENCES champions(id),
    PRIMARY KEY (match_id, summoner_id)
);