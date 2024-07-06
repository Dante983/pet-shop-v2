<?php

namespace App\Guards;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Exception;

class JwtGuard implements Guard
{
    use GuardHelpers;

    protected $request;
    protected $inputKey = 'api_token';
    protected $storageKey = 'api_token';
    protected $user;

    public function __construct(UserProvider $provider, Request $request)
    {
        $this->provider = $provider;
        $this->request = $request;
    }

    public function user()
    {
        if (!is_null($this->user)) {
            return $this->user;
        }

        $token = $this->getTokenForRequest();
        if (empty($token)) {
            return null;
        }

        $secretKey = env('JWT_KEY');
        try {
            $credentials = JWT::decode($token, new Key($secretKey, 'HS512'));
            $this->user = $this->provider->retrieveById($credentials->data->userID);
        } catch (Exception $e) {
            return null;
        }

        return $this->user;
    }

    public function getTokenForRequest()
    {
        $token = $this->request->query($this->inputKey);

        if (empty($token)) {
            $token = $this->request->input($this->inputKey);
        }

        if (empty($token)) {
            $token = $this->request->bearerToken();
        }

        return $token;
    }

    public function validate(array $credentials = [])
    {
        if (empty($credentials[$this->inputKey])) {
            return false;
        }

        $this->user = $this->provider->retrieveById($credentials[$this->inputKey]);

        return !is_null($this->user);
    }
}
