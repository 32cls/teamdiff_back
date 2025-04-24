<?php

namespace App\Http\Controller;

use Illuminate\Routing\Controller;

class AuthController extends Controller
{
    public function index() {

        return response()->json("https://auth.riotgames.com?redirect_uri=&client_id=");
    }
}
