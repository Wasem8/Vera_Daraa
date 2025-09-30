<?php

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use App\Http\Responses\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (UnauthorizedException $e, $request) {
            if ($request->expectsJson()) {
                return Response::Error(
                    false,
                    'you dont have permission to access this route',
                    403
                );
            }

        });

        $exceptions->render(function (ModelNotFoundException $e, $request) {
            return Response::Error(
                false,
                'Model not found',
                404
            );
        });

        $exceptions->render(function (NotFoundHttpException $e, $request) {
            if ($request->expectsJson()) {
                return Response::Error(
                    false,
                    'Resource not found',
                    404
                );
            }
        });

    })->create();
