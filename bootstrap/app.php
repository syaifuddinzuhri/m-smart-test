<?php

use App\Exceptions\ApiExceptionHandler;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
        apiPrefix: '',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies(at: '*');
        $middleware->statefulApi();
        $middleware->encryptCookies(except: [
            'ms_supervisor_session',
            'ms_admin_session',
            'ms_student_session',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        ApiExceptionHandler::register($exceptions);
    })->create();
