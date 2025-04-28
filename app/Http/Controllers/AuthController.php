<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    private string $provider;

    private string $clientId;

    private string $clientSecret;

    private string $callback;

    public function __construct()
    {
        $this->provider = config('riot.provider');
        $this->clientId = config('riot.clientId');
        $this->clientSecret = config('riot.clientSecret');
        $this->callback = url('/oauth2-callback');
    }

    public function index(): JsonResponse
    {
        return response()->json("$this->provider?redirect_uri=$this->callback&client_id=$this->clientId&response_type=code&scope=openid");
    }

    /**
     * @throws RequestException
     * @throws ConnectionException
     */
    public function oauth2Callback(Request $request): void
    {
        $code = $request->query('code');
        $response = Http::asForm()->acceptJson()->withBasicAuth($this->clientId, $this->clientSecret)->post(
            "$this->provider/token",
            [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->callback,
            ]
        );
        $response->throw();
        $json = $response->json();
        $refreshToken = $json['refresh_token'];
        $idToken = $json['id_token'];
        $accessToken = $json['access_token'];
    }
}
