<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if(auth()->user()->token()->name === 'admin_jwt') {
            return $next($request);
        }
        return response()->json(['message' => 'Unauthenticated Admin.'], 403);
    }
}
