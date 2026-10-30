<?php

namespace App\Application\Identity;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class AuthenticateAccount
{
    public function register(array $data): User
    {
        return User::create($data);
    }

    public function login(array $credentials): void
    {
        if (!Auth::guard('web')->attempt($credentials)) {
            throw ValidationException::withMessages(['email' => 'The supplied credentials are invalid.']);
        }
    }
}
