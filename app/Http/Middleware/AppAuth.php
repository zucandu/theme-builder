<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AppAuth
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
        if(auth()->user()->token()->name === 'developer_jwt') {
            return $next($request);
        }
        return response()->json(['message' => 'Unauthenticated App.'], 403);
    }
}
