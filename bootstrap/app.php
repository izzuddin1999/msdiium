<?php

use App\Http\Middleware\UseViewerSession;
use App\Console\Commands\SendDocumentExpiryReminders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([SendDocumentExpiryReminders::class])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            UseViewerSession::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
