<?php

namespace App\Http\Middleware;

use App\Infrastructure\Auditing\DatabaseRequestLogger;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class LogRequests
{
    public function __construct(private readonly DatabaseRequestLogger $logger) {}

    public function handle(Request $request, Closure $next): Response
    {
        $id = (string) Str::uuid();
        $request->attributes->set('audit_request_id', $id);
        $request->attributes->set('audit_started', microtime(true));
        $this->logger->start($id, $request->method(), $request->ip());

        $response = $next($request);
        $response->headers->set('X-Request-ID', $id);

        return $response;
    }

    public function terminate(Request $request, Response $response): void
    {
        $id = $request->attributes->get('audit_request_id');
        if (!$id) {
            return;
        }

        $this->logger->finish(
            $id,
            $request->route()?->uri() ?? 'unmatched',
            $response->getStatusCode(),
            (int) ((microtime(true) - $request->attributes->get('audit_started')) * 1000),
        );
    }
}
