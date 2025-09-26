<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureOrganizationIsAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('organizacion_id')) {
            return redirect()->route('organizacion.login.form')->with('status', 'Debes iniciar sesión.');
        }
        return $next($request);
    }
}

