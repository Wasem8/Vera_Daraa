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
        then: function (){
            //client
            Route::middleware('api')
                ->prefix('client')
                ->group(base_path('routes/vera/client.php'));

            // admin + receptionist
            Route::middleware('api')
                ->prefix('web')
                ->group(base_path('routes/vera/web.php'));
        }
    )
    ->withMiddleware(function (Middleware $middleware) {

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
