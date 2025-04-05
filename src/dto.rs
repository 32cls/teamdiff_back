use serde::Deserialize;

#[derive(Deserialize, Debug)]
pub struct AccountDto {
    pub puuid: String,
    #[serde(rename = "gameName")]
    pub gamename: String,
    #[serde(rename = "tagLine")]
    pub tagline: String
}

#[derive(Deserialize, Debug)]
pub struct SummonerDto {
    #[serde(rename = "profileIconId")]
    pub profile_icon_id: i32,
    pub id: String,
    #[serde(rename = "summonerLevel")]
    pub summoner_level: i32,
    #[serde(rename = "revisionDate")]
    pub revision_date: i64
}

#[derive(Deserialize, Debug)]
pub struct MatchDto {
    pub metadata: MetadataDto,
    pub info: InfoDto
}

#[derive(Deserialize, Debug)]
pub struct MetadataDto {
    #[serde(rename = "matchId")]
    pub match_id: String,
}

#[derive(Deserialize, Debug)]
pub struct InfoDto {
    #[serde(rename = "gameDuration")]
    pub game_duration: i64,
    pub participants: Vec<ParticipantDto>
}

#[derive(Deserialize, Debug)]
pub struct ParticipantDto {
    pub puuid: String,
    #[serde(rename = "championId")]
    pub champion_id: i32,
    #[serde(rename = "teamId")]
    pub team_id: i32,
    pub win: bool,
    pub kills: i32,
    pub deaths: i32,
    pub assists: i32,
    #[serde(rename = "champLevel")]
    pub champ_level: i32,
    #[serde(rename = "teamPosition")]
    pub team_position: String,
}

