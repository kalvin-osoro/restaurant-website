<?php
use App\Http\Middleware\EnsureAdmin;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(web: __DIR__.'/../routes/web.php', api: __DIR__.'/../routes/api.php', commands: __DIR__.'/../routes/console.php', health: '/up', apiPrefix: 'api/v1')
    ->withMiddleware(function (Middleware $middleware): void {
        // Resolve the actual client before hashing its IP in the mandatory audit log.
        $middleware->prepend([
            \Illuminate\Http\Middleware\TrustProxies::class,
            \App\Http\Middleware\LogRequests::class,
        ]);
        $middleware->alias(['admin' => EnsureAdmin::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {})
    ->create();
