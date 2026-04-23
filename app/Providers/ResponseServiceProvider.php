<?php

namespace App\Providers;

use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Response::macro('success', function ($data = null, $message = 'Success', $code = 200) {
            return response()->json([
                'status' => true,
                'message' => $message,
                'data' => $data,
            ], $code);
        });

        Response::macro('error', function ($message = 'Error', $code = 400, $errors = [], $debug = null) {
            $response = [
                'status' => false,
                'message' => $message,
            ];

            if (count($errors) > 0) {
                $response['errors'] = $errors;
            }

            if ($debug && config('app.debug')) {
                $response['_debug'] = $debug;
            }

            return response()->json($response, $code);
        });
    }
}
