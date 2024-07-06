<?php

namespace App\Http\Middleware;

use App\Helpers\PublicHelper;
use Illuminate\Http\Request;
use Closure;

class JWTVerify
{
    public function handle(Request $request, Closure $next)
    {
        $publicHelper = new PublicHelper();

        try {
            $token = $publicHelper->GetAndDecodeJWT();
            $request->merge(['user' => $token->data->userID]);  // Attach the user ID to the request
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 401);
        }

        return $next($request);
    }
}
