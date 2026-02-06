<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenExpiredException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;

class JwtAuthenticate
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();

            if (!$user) {
                return response()->json(['message' => 'User not found'], 401);
            }
        } catch (TokenExpiredException) {
            return response()->json(['message' => 'Token expired'], 401);
        } catch (TokenInvalidException) {
            return response()->json(['message' => 'Token invalid'], 401);
        } catch (JWTException) {
            return response()->json(['message' => 'Token not provided'], 401);
        }

        return $next($request);
    }
}
