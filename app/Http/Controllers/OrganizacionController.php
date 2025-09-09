<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Beneficiario;
use App\Models\Formulario;
use Illuminate\Support\Facades\Auth; // si usas Auth

class OrganizacionController extends Controller
{
    // Mostrar el formulario de registro de beneficiarios
    public function showForm()
    {
        // Obtenemos la organización logueada (ejemplo usando sesión)
        $organizacion_id = session('organizacion_id'); // o Auth::id() si usas guard custom

        // Tomamos el formulario abierto de la organización
        $formulario = Formulario::where('organizacion_id', $organizacion_id)
                                ->where('estado', 'abierto')
                                ->latest('fecha_creacion')
                                ->first();

        // Beneficiarios recientes de la organización
        $beneficiarios = Beneficiario::whereHas('formulario', function($q) use ($organizacion_id){
            $q->where('organizacion_id', $organizacion_id);
        })->latest('fecha_creacion')->take(10)->get();

        return view('organizaciones.formulario', compact('beneficiarios', 'formulario'));
    }

    public function storeBeneficiario(Request $request)
    {
        $request->validate([
            'rut' => 'required',
            'nombre_completo' => 'required',
            'fecha_nacimiento' => 'required|date',
            'sexo' => 'nullable|in:M,F,U',
            'formulario_id' => 'required|exists:formularios,id',
        ]);

        $beneficiario = Beneficiario::create([
            'rut' => $request->rut,
            'nombre_completo' => $request->nombre_completo,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'sexo' => $request->sexo,
            'formulario_id' => $request->formulario_id,
        ]);

        // Traer últimos 10 beneficiarios para mostrar
        $beneficiarios = Beneficiario::with('formulario')->latest('fecha_creacion')->take(10)->get();
        $tipos = TipoOrganizacion::where('usable', 1)->get();

        return view('organizaciones.formulario', [
            'beneficiarios' => $beneficiarios,
            'tipos' => $tipos,
            'success_ben' => 'Beneficiario registrado correctamente.'
        ]);
    }


}
