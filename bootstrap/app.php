
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up'
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust the forwarding headers supplied by the local Ngrok tunnel.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'child' => \App\Http\Middleware\EnsureChildContext::class,
            'parent' => \App\Http\Middleware\EnsureParentContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontReport(\App\Exceptions\ContentLockedException::class);

        $exceptions->render(function (
            \App\Exceptions\ContentLockedException $e,
            \Illuminate\Http\Request $request
        ) {
            return response()->view('errors.content-locked', [], 403);
        });
    })
    ->create();
