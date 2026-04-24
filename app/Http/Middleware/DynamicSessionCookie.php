<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class DynamicSessionCookie
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();

        $adminHost = str_replace(['http://', 'https://'], '', config('app.admin_domain'));
        $studentHost = str_replace(['http://', 'https://'], '', config('app.student_domain'));
        $supervisorHost = str_replace(['http://', 'https://'], '', config('app.supervisor_domain'));

        if ($host === $adminHost) {
            Config::set('session.cookie', 'ms_admin_session');
        } elseif ($host === $studentHost) {
            Config::set('session.cookie', 'ms_student_session');
        } elseif ($host === $supervisorHost) {
            Config::set('session.cookie', 'ms_supervisor_session');
        }

        return $next($request);
    }
}
