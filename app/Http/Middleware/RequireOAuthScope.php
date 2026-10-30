<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireOAuthScope
{
    public function handle(Request $request, Closure $next, string $scope): Response
    {
        abort_unless($request->user()?->tokenCan($scope), 403, 'The access token does not grant the required scope.');
        return $next($request);
    }
}
