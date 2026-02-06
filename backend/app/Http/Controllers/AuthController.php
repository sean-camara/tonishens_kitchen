<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => $request->password, // Auto-hashed by cast
            'role'       => 'customer',
        ]);

        $token = JWTAuth::fromUser($user);

        return $this->respondWithToken($token, $user, 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return $this->error('Invalid email or password', 401);
        }

        $user = auth()->user();

        return $this->respondWithToken($token, $user);
    }

    public function me(): JsonResponse
    {
        return $this->success(auth()->user());
    }

    public function logout(): JsonResponse
    {
        auth()->logout();

        return $this->success(null, 'Logged out');
    }

    public function refresh(): JsonResponse
    {
        try {
            $newToken = auth()->refresh();
            return $this->respondWithToken($newToken, auth()->user());
        } catch (\Exception $e) {
            return $this->error('Could not refresh token', 401);
        }
    }

    private function respondWithToken(string $token, User $user, int $code = 200): JsonResponse
    {
        return response()->json([
            'success'      => true,
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => config('jwt.ttl') * 60,
            'user'         => $user,
        ], $code);
    }
}
