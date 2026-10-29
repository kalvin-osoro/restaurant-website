<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthorizeContentWrite
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('website-content.write_token');
        abort_unless(is_string($token) && strlen($token) >= 32, 503, 'Content writes are not configured.');
        $provided = $request->bearerToken();
        abort_unless(is_string($provided) && hash_equals($token, $provided), 401, 'Invalid content write credentials.');
        return $next($request);
    }
}
