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
    pub summoner_level: i32
}
