<?php

namespace App\Http\Middleware;

use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token\Parser;
use Closure;
use DateTimeImmutable;
use Exception;

class ValidateJwtToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        $token = $request->bearerToken();

        $key = substr(config('app.key'), 7);

        if (!$token) {
            return response()->json(['message' => 'Unauthorized!'], 401);
        }

        try {
            $parser = new Parser(new JoseEncoder());
            $parsedToken = $parser->parse($token);
            // $parsedToken = (new Parser())->parse($token);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid token'], 401);
        }

        $signer = new Sha256();
        $secretKey = $key;

        if (!$parsedToken->isPermittedFor(config('app.url'))) {
            throw new Exception('Invalid token', 1);
        }

        if (!$parsedToken->hasBeenIssuedBy(config('app.url'))) {
            throw new Exception('Invalid token 2', 1);
        }

        if ($parsedToken->isExpired(new DateTimeImmutable('now'))) {
            throw new Exception('Invalid token 3', 1);
        }

        $uid = $parsedToken->claims()->get('uid');

        $request->merge(['uid' => $uid]);

        return $next($request);
    }
}
