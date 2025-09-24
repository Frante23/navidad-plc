<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOrganizationIsAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->session()->get('organizacion_id')) {
            return redirect('/')->with('status', 'Debes iniciar sesión.');
        }

        return $next($request);
    }
}
