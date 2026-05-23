<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.auth' => \App\Http\Middleware\AdminAuth::class,
            'super.admin' => \App\Http\Middleware\SuperAdminMiddleware::class,
            'onboarded' => \App\Http\Middleware\RedirectIfOnboardingNotCompleted::class,
            'subscribed' => \App\Http\Middleware\CheckSubscription::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\TenantMiddleware::class,
        ]);
        $middleware->api(append: [
            \App\Http\Middleware\TenantMiddleware::class,
        ]);
        $middleware->validateCsrfTokens(except: [
            'webhooks/razorpay',
        ]);

        $middleware->redirectGuestsTo(function (\Illuminate\Http\Request $request) {
            if ($request->is('admin') || $request->is('admin/*')) {
                return route('admin.login');
            }
            return route('login');
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
