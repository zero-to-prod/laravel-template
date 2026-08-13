<?php

use App\Helpers\HttpHeader;
use App\Helpers\Role;
use App\Http\Middleware\EnsureEmailIsVerifiedMiddleware;
use App\Http\Middleware\EnsureTokenAbilityMiddleware;
use App\Routes\MiddlewareTag;
use App\Routes\Web;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        using: function () {
            Route::middleware([MiddlewareTag::web->value, 'throttle:5,1'])
                ->group(base_path('routes/web.php'));

            Route::middleware([MiddlewareTag::web->value, MiddlewareTag::auth->value])
                ->group(base_path('routes/web_auth.php'));

            Route::middleware([MiddlewareTag::web->value, MiddlewareTag::auth->value, Role::admin->middleware()])
                ->group(base_path('routes/web_admin.php'));

            Route::middleware(MiddlewareTag::api->value)
                ->group(base_path('routes/api.php'));

            Route::middleware([MiddlewareTag::sanctum->value, EnsureTokenAbilityMiddleware::class])
                ->group(base_path('routes/api_auth.php'));
        },
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo(static fn () => Web::login->value);
        $middleware->alias([
            MiddlewareTag::verified->value => EnsureEmailIsVerifiedMiddleware::class,
            MiddlewareTag::role->value => RoleMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->hasHeader(HttpHeader::HxRequest->value)) {
                return response()->noContent(401)->header(HttpHeader::HxRedirect->value, Web::login->value);
            }
        });
    })->create();
