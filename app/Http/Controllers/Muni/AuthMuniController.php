<?php

namespace App\Http\Controllers\Muni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FuncionarioMunicipal;
use Illuminate\Support\Facades\DB;
use App\Support\Audit; 

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

        if (Auth::guard('func')->attempt($cred, $request->boolean('remember'))) {
            $request->session()->regenerate();

            FuncionarioMunicipal::where('correo', $cred['correo'])
                ->update(['last_login_at' => now()]);

            $uid = Auth::guard('func')->id();
            Audit::log($uid !== null ? (int)$uid : null, 'AUTH_LOGIN', 'auth', null, 'Login OK');

            return redirect()->route('muni.dashboard');
        }

        return back()->withErrors(['correo' => 'Credenciales inválidas'])->onlyInput('correo');
    }

    public function logout(Request $request)
    {
        $uid = Auth::guard('func')->id();
        Audit::log($uid !== null ? (int)$uid : null, 'AUTH_LOGOUT', 'auth', null, 'Logout');

        Auth::guard('func')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.funcionarios')
            ->with('status', 'Sesión finalizada.');
    }

}
