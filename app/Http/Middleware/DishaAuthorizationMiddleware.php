<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DishaAuthorizationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $bearerTokenCheck = 'Bearer '.env('DISHA_BEARER_TOKEN') ?? 'a1b2cfre';
        $token = $request->header('Authorization');

        // Example:
        // Authorization: Bearer YOUR_SECRET_TOKEN

        if (!$token || $token !== $bearerTokenCheck) {
            return response()->json([
                'status' => 0,
                'message' => 'Unauthorized access.',
                'result_status' => false,
                'result_data' => [],
            ], 401);
        }

        return $next($request);
    }
}
