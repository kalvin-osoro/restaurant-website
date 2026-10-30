<?php

namespace App\Http\Controllers;

use App\Application\Identity\AuthenticateAccount;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\{RedirectResponse, Request};
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OAuthSessionController extends Controller
{
    public function show(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request, AuthenticateAccount $auth): RedirectResponse
    {
        $auth->login($request->validated());
        $request->session()->regenerate();
        return redirect()->intended('/');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
