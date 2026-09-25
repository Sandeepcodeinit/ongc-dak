<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicyMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        /*
        $response->headers->set('Content-Security-Policy', 
            "default-src 'self'; " .
            "script-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com https://cdn.datatables.net https://code.highcharts.com; " .
            "style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com https://cdn.datatables.net https://code.highcharts.com; " .
            "font-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.gstatic.com https://fonts.gstatic.comframe-ancestors;" .
            "img-src 'self' http://www.w3.org/2000/svg data:;".
            "frame-ancestors 'self';"
        );*/
        $response->headers->set('Content-Security-Policy', "default-src 'self'; script-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com https://cdn.datatables.net https://code.highcharts.com; style-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.googleapis.com https://cdn.datatables.net https://code.highcharts.com; font-src 'self' 'unsafe-inline' https://fonts.bunny.net https://fonts.gstatic.com; img-src 'self' http://www.w3.org/2000/svg data:; frame-ancestors 'self';");
        return $response;
    }
}
