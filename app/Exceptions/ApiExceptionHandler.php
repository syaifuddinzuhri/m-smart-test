<?php

namespace App\Exceptions;

use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class ApiExceptionHandler
{
    public static function register(Exceptions $exceptions)
    {
        $exceptions->render(function (Throwable $e, Request $request) {

            if ($request->is('api/*') || $request->getHost() === env('API_DOMAIN')) {

                $status = 500;
                if ($e instanceof HttpExceptionInterface) {
                    $status = $e->getStatusCode();
                } elseif ($e instanceof ValidationException) {
                    $status = 422;
                } elseif ($e instanceof AuthenticationException) {
                    $status = 401;
                }

                if ($status >= 500) {
                    Log::error('API Error: ' . $e->getMessage(), [
                        'url' => $request->fullUrl(),
                        'method' => $request->method(),
                        'input' => $request->except(['password', 'password_confirmation']),
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }

                $message = $e->getMessage() ?: 'Internal Server Error';
                $errors = [];

                if ($e instanceof ValidationException) {
                    $message = 'Data yang dikirim tidak valid.';
                    $errors = $e->errors();
                }

                $debugData = null;
                if (config('app.debug')) {
                    $debugData = [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => collect($e->getTrace())->take(3),
                    ];
                }

                return response()->error($message, $status, $errors, $debugData);
            }
        });
    }
}
