<?php

namespace App\Http\Middleware;
use Illuminate\Session\TokenMismatchException;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'whatsapp/webhook',
        'api/whatsapp/webhook',
        'whatsapp/fallback',
        'whatsapp/status',
    ];

    public function handle($request, Closure $next)
    {
        try {
            return parent::handle($request, $next);
        } catch (TokenMismatchException $exception) {
            // Redirect to the login page
            return redirect()->route('login')->with('error', 'Session expired. Please try logging in again.');
        }
    }

    protected function addCookieToResponse($request, $response)
    {
        $config = config('session');

        $response->headers->setCookie(
            cookie(
                'XSRF-TOKEN', $request->session()->token(), 120, 
                $config['path'], $config['domain'], 
                $config['secure'], true,  // Here `true` sets HttpOnly flag
                false, $config['same_site'] ?? null
            )
        );

        return $response;
    }

}
