<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureFuncionarioIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth('func')->user();
        if (!$user || !$user->es_admin) {
            abort(403, 'Acceso solo para administradores.');
        }
        return $next($request);
    }
}
