<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role  The required role to access the route
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        // Check if the user is authenticated and has the required role
        if (!Auth::check() || !Auth::user()->hasRole($role)) {
            // Respond with unauthorized error if the user does not have the required role
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return $next($request);
    }
}
