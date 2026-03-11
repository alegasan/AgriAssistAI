<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureClient;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin' => EnsureAdmin::class,
            'client' => EnsureClient::class,
        ]);
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (ThrottleRequestsException $exception, Request $request) {
            $retryAfterSeconds = (int) ($exception->getHeaders()['Retry-After'] ?? 60);
            $waitInMinutes = max(1, (int) ceil($retryAfterSeconds / 60));

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => "Too many attempts. Try again in {$waitInMinutes} minute(s).",
                    'retry_after' => $retryAfterSeconds,
                ], 429, $exception->getHeaders());
            }

            
            $errorKey = $request->routeIs('login.*') ? 'login' : 'email';

            return back()
                ->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors([
                    $errorKey => "Too many attempts. Try again in {$waitInMinutes} minute(s).",
                ]);
        });
    })->create();