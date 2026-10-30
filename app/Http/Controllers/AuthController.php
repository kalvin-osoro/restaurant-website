<?php

namespace App\Http\Controllers;

use App\Application\Identity\AuthenticateAccount;
use App\Http\Requests\RegisterAccountRequest;
use App\Http\Resources\AccountResource;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function register(RegisterAccountRequest $request, AuthenticateAccount $auth): JsonResponse
    {
        $user = $auth->register($request->safe()->only(['name', 'email', 'password']));
        return response()->json(['data' => (new AccountResource($user))->resolve()], 201);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();
        DB::transaction(function () use ($token) {
            $token->revoke();
            $token->refreshToken()->update(['revoked' => true]);
        });
        return response()->json(['message' => 'Logged out.']);
    }
}
