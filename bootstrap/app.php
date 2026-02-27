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
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
            'active_role' => \App\Http\Middleware\ActiveRoleMiddleware::class, // PROMPT 96
            'api.session' => \App\Http\Middleware\ApiSessionAuth::class, // Support session auth in API routes
            'vendor.onboarding.complete' => \App\Http\Middleware\EnsureVendorOnboardingComplete::class,
            'vendor.approved' => \App\Http\Middleware\EnsureVendorOnboardingApproved::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\EnsureActiveRole::class,
            \App\Http\Middleware\CheckVendorStatus::class, // 👈 Add this
        ]);

        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();