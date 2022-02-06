<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class JrpcServerToken
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (strcmp(config('services.jrpc.token'), $request->bearerToken()) !== 0) {
            abort(401);
        }

        return $next($request);
    }
}
