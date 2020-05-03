<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\JsonResponse;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->user()->role != 1)
            return response()->json(['error' => 'unauthorized_client'], JsonResponse::HTTP_UNAUTHORIZED);
        else
            return $next($request);
    }
}
