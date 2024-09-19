<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SuperAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
               // Check if the authenticated user has the admin role
       if (auth()->user()->user_type_id!= '4') {
        return response()->json(['error' => 'Opps! You do not have permission to access.'], 403);
    }
        return $next($request);
    }
}
