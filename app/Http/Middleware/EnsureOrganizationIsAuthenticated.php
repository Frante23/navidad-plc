<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Organizacion;

class EnsureOrganizationIsAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        $orgId = $request->session()->get('organizacion_id');
        if (!$orgId) {
            return redirect('/')->with('status', 'Debes iniciar sesión.');
        }

        $org = Organizacion::find($orgId);
        if (!$org) {
            $request->session()->forget('organizacion_id');
            return redirect('/')->with('status', 'Sesión inválida. Inicia sesión nuevamente.');
        }

        if ($org->estado !== 'activo') {
            $request->session()->forget('organizacion_id');
            $mensaje = $org->estado === 'pendiente'
                ? 'Tu organización volvió a estado pendiente. Debe ser habilitada por un funcionario.'
                : 'Tu organización fue desactivada. Contacta a la Municipalidad.';
            return redirect('/')->with('status', $mensaje);
        }

        return $next($request);
    }
}
