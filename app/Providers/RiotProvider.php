<?php

declare(strict_types=1);

namespace App\Providers;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;
use SocialiteProviders\Manager\OAuth2\AbstractProvider;

class RiotProvider extends AbstractProvider implements ProviderInterface
{
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(config('services.riot.base_url') . '/oauth/authorize', $state);
    }

    protected function getTokenUrl(): string
    {
        return config('services.riot.base_url') . '/oauth/token';
    }

    /**
     * @throws GuzzleException
     */
    protected function getUserByToken(#[\SensitiveParameter] $token)
    {
        $response = $this->getHttpClient()
            ->get(config('services.riot.base_url') . '/api/me', [
                RequestOptions::HEADERS => [
                    'Accepts' => 'application/json',
                    'Authorization' => "Bearer {$token}",
                ],
            ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function mapUserToObject(array $user): User
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['puuid'],
            'nickname' => $user['gameName'],
            'name' => "{$user['gameName']} {$user['tagLine']}",
        ]);
    }
}
