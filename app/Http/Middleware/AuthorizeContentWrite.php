<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Gate};
use Symfony\Component\HttpFoundation\Response;

class AuthorizeContentWrite
{
    public function handle(Request $request, Closure $next): Response
    {
        $provided = $request->bearerToken();
        // OAuth2 JWTs always go through Passport validation, even if invalid or expired.
        if (is_string($provided) && substr_count($provided, '.') === 2) {
            $user = Auth::guard('api')->user();
            abort_unless($user, 401, 'Invalid or expired access token.');
            abort_unless($user->tokenCan('content.write'), 403, 'The access token does not grant content.write.');
            Gate::forUser($user)->authorize('content.write');
            return $next($request);
        }
        // Separate, content-only credential retained for existing server integrations.
        $token = config('website-content.write_token');
        abort_unless(is_string($token) && strlen($token) >= 32, 503, 'Content writes are not configured.');
        abort_unless(is_string($provided) && hash_equals($token, $provided), 401, 'Invalid content write credentials.');
        return $next($request);
    }
}
