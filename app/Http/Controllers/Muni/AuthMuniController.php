<?php

namespace App\Http\Controllers\Muni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FuncionarioMunicipal;
use Illuminate\Support\Facades\DB;

class AuthMuniController extends Controller
{
    public function showLoginForm()
    {
        return view('municipales.login');
    }

    public function login(Request $request)
    {
        $cred = $request->validate([
            'correo'   => 'required|email',
            'password' => 'required',
        ]);

        // intenta login con guard 'func'
        if (Auth::guard('func')->attempt($cred, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // marca último login
            FuncionarioMunicipal::where('correo', $cred['correo'])
                ->update(['last_login_at' => now()]);

            return redirect()->route('muni.dashboard');
        }

        return back()->withErrors(['correo' => 'Credenciales inválidas'])->onlyInput('correo');
    }

    public function logout(Request $request)
    {
        Auth::guard('func')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.funcionarios')
            ->with('status', 'Sesión finalizada.');
    }
}
