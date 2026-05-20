<?php

use App\Http\Middleware\EnsureAdmin;
use App\Http\Middleware\EnsureClinicalOperationsScope;
use App\Http\Middleware\EnsureUserActive;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin'            => EnsureAdmin::class,
            'user.active'      => EnsureUserActive::class,
            'clinical.scope'   => EnsureClinicalOperationsScope::class,
        ]);

        // Apply both checks to every authenticated web request
        $middleware->appendToGroup('web', EnsureUserActive::class);
        $middleware->appendToGroup('web', EnsureClinicalOperationsScope::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
