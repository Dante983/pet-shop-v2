<?php

namespace App\Handlers;

use Firebase\JWT\JWT;
use Illuminate\Support\Facades\App;
use DateTimeImmutable;

class AuthHandler
{
    /** Handles operations related to admin authentication */

    // generate token
    public function GenerateToken($user)
    {
        // $secretKey = env('JWT_KEY');
        $secretKey = config('app.jwt_key');
        // var_dump($secretKey);
        // if (!$secretKey) {
        //
        // }
        $tokenId = base64_encode(random_bytes(16));
        $issuedAt = new DateTimeImmutable();
        $expire = $issuedAt->modify('+6 minutes')->getTimestamp();  // Add 60 seconds
        $serverName = 'your.pbn.name';
        $userID = $user->id;

        // Create the token as an array
        $data = [
            'iat' => $issuedAt->getTimestamp(),  // Issued at: time when the token was generated
            'jti' => $tokenId,  // Json Token Id: an unique identifier for the token
            'iss' => $serverName,  // Issuer
            'nbf' => $issuedAt->getTimestamp(),  // Not before
            'exp' => $expire,  // Expire
            'data' => [  // Data related to the signer user
                'userID' => $userID,  // User name
            ]
        ];

        $token = JWT::encode(
            $data,
            $secretKey,
            'HS512'
        );
        return $token;
    }
}
