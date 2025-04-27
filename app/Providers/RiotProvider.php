<?php

namespace App\Providers;

use Laravel\Socialite\Two\User;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;

class RiotProvider extends AbstractProvider
{

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://auth.riotgames.com/authorize', $state);
    }

    protected function getTokenUrl()
    {
        return 'https://auth.riotgames.com/token';
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://europe.api.riotgames.com/riot/account/v1/accounts/me', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
            ],
        ]);

        return json_decode($response->getBody(), true);
    }

    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'       => $user['puuid'],
            'nickname' => $user['gameName'],
            'name'     => sprintf("%s#%s", $user['gameName'], $user['tagLine']),
            'email'    => null,
            'avatar'   => null,
        ]);
    }
}
