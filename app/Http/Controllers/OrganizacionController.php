<?php

namespace App\Http\Controllers;

use App\Models\Beneficiario;
use App\Models\Formulario;
use App\Models\Organizacion;
use App\Models\Periodo; // <- importar correctamente aquí
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

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

    // Mostrar formulario y beneficiarios recientes
    public function showForm()
    {
        $organizacion_id = session('organizacion_id'); 

        // Buscar un periodo abierto
        $periodo = Periodo::where('estado', 'abierto')->latest('anio')->first();

        if (!$periodo) {
            abort(500, 'No hay periodos abiertos.');
        }

        // Buscar formulario abierto para la organización y periodo
        $formulario = Formulario::where('organizacion_id', $organizacion_id)
                                ->where('estado', 'abierto')
                                ->where('periodo_id', $periodo->id)
                                ->latest('created_at')
                                ->first();

        if (!$formulario) {
            $periodo = Periodo::first(); // O el último abierto con where('estado','abierto')

            $formulario = Formulario::create([
                'organizacion_id' => 1, // o session('organizacion_id')
                'estado' => 'abierto',
                'periodo_id' => $periodo->id,
            ]);

        }

        $beneficiarios = Beneficiario::where('formulario_id', $formulario->id)
                                ->latest()
                                ->get();

        return view('organizaciones.formulario', compact('beneficiarios', 'formulario'));
    }

    // Guardar beneficiario
    // Guardar beneficiario
    public function storeBeneficiario(Request $request)
    {
        $request->validate([
            'rut' => 'required',
            'nombre_completo' => 'required',
            'fecha_nacimiento' => 'required|date',
            'sexo' => 'nullable|in:M,F,U',
            'direccion' => 'required|string',
            'formulario_id' => 'required|exists:formularios,id',
        ]);

        Beneficiario::create([
            'rut' => $request->rut,
            'nombre_completo' => $request->nombre_completo,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'sexo' => $request->sexo,
            'direccion' => $request->direccion,
            'formulario_id' => $request->formulario_id,
            'organizacion_id' => session('organizacion_id'), 
        ]);


        return redirect()->route('formulario')
                        ->with('success_ben', 'Beneficiario registrado correctamente.');
    }

}
