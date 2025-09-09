<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Beneficiario;
use App\Models\Formulario;
use App\Models\Organizacion;
use Illuminate\Support\Facades\Hash;

class OrganizacionController extends Controller
{
    // Login de organización
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'clave' => 'required',
        ]);

        $org = Organizacion::where('email', $request->email)->first();

        if (!$org || !Hash::check($request->clave, $org->clave)) {
            return back()->withErrors(['email' => 'Credenciales incorrectas']);
        }

        session(['organizacion_id' => $org->id]);

        return redirect()->route('formulario');
    }

    // Mostrar formulario
    public function showForm()
    {
        $organizacion_id = session('organizacion_id');

        if (!$organizacion_id) {
            return redirect()->route('login.organizacion');
        }

        $formulario = Formulario::where('organizacion_id', $organizacion_id)
                                ->where('estado', 'abierto')
                                ->latest('fecha_creacion')
                                ->first();

        $beneficiarios = Beneficiario::whereHas('formulario', function($q) use ($organizacion_id) {
            $q->where('organizacion_id', $organizacion_id);
        })->latest('fecha_creacion')->take(10)->get();

        return view('organizaciones.formulario', compact('beneficiarios', 'formulario'));
    }

    // Guardar beneficiario
    public function storeBeneficiario(Request $request)
    {
        $request->validate([
            'rut' => 'required',
            'nombre_completo' => 'required',
            'fecha_nacimiento' => 'required|date',
            'sexo' => 'nullable|in:M,F,U',
            'formulario_id' => 'required|exists:formularios,id',
        ]);

        Beneficiario::create($request->all());

        return back()->with('success_ben', 'Beneficiario registrado correctamente.');
    }
}
