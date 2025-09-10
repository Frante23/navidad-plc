<?php

namespace App\Http\Controllers;

use App\Models\Beneficiario;
use App\Models\Formulario;
use App\Models\Organizacion;
use App\Models\Periodo; 
use App\Models\TramoEdad;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;

class OrganizacionController extends Controller
{
    // Login de organización
    public function login(Request $request)
    {
        $request->validate([
        'personalidad_juridica' => 'required|string',
        'clave' => 'required',
    ]);

    $org = Organizacion::where('personalidad_juridica', $request->personalidad_juridica)->first();

    if (!$org || !Hash::check($request->clave, $org->clave)) {
        return back()->withErrors(['personalidad_juridica' => 'Credenciales incorrectas']);
    }


        session(['organizacion_id' => $org->id]);

        return redirect()->route('formulario');
    }

    public function showForm()
    {
        $organizacion_id = session('organizacion_id'); 

        $periodo = Periodo::where('estado', 'abierto')->latest('anio')->first();

        if (!$periodo) {
            abort(500, 'No hay periodos abiertos.');
        }

        $formulario = Formulario::where('organizacion_id', $organizacion_id)
                                ->where('estado', 'abierto')
                                ->where('periodo_id', $periodo->id)
                                ->latest('created_at')
                                ->first();

        if (!$formulario) {
            $periodo = Periodo::first(); 

            $formulario = Formulario::create([
                'organizacion_id' => 1, 
                'estado' => 'abierto',
                'periodo_id' => $periodo->id,
            ]);

        }

        $beneficiarios = Beneficiario::where('formulario_id', $formulario->id)
                                ->latest()
                                ->get();

        return view('organizaciones.formulario', compact('beneficiarios', 'formulario'));
    }



    private function cleanRut($rut)
    {
        return preg_replace('/[^0-9kK]/', '', $rut); 
    }

    private function validateRut($rut)
    {
        $rut = $this->cleanRut($rut);
        if (strlen($rut) < 2) return false;

        $dv = strtoupper(substr($rut, -1));
        $num = substr($rut, 0, -1);

        $s = 1;
        $m = 0;
        for (; $num != 0; $num = floor($num / 10)) {
            $s = ($s + $num % 10 * (9 - $m++ % 6)) % 11;
        }
        $dvEsperado = $s ? strval($s - 1) : 'K';

        return $dv === $dvEsperado;
    }


    
    
    public function storeBeneficiario(Request $request)
    {

        $rutLimpio = $this->cleanRut($request->rut);
        if (!$this->validateRut($rutLimpio)) {
            return back()->withErrors(['rut' => "RUT inválido: $rutLimpio"])->withInput();
        }


        $request->validate([
            'nombre_completo' => 'required',
            'fecha_nacimiento' => 'required|date',
            'sexo' => 'nullable|in:M,F,U',
            'formulario_id' => 'required|exists:formularios,id',
            'direccion' => 'required',
        ]);


        $edadMeses = \Carbon\Carbon::parse($request->fecha_nacimiento)->diffInMonths(now());


        $tramo = TramoEdad::where('edad_min_meses', '<=', $edadMeses)
                        ->where('edad_max_meses', '>=', $edadMeses)
                        ->first();


        if (!$tramo) {
            return back()->withErrors([
                'edad' => "No existe un tramo de edad para este beneficiario."
            ])->withInput();
        }


        Beneficiario::create([
            'rut' => $rutLimpio,
            'nombre_completo' => $request->nombre_completo,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'sexo' => $request->sexo,
            'direccion' => $request->direccion,
            'formulario_id' => $request->formulario_id,
            'organizacion_id' => session('organizacion_id'),
            'tramo_id' => $tramo->id,
        ]);

        return redirect()->route('formulario')
                        ->with('success_ben', 'Beneficiario registrado correctamente.');
    }


    public function cerrar(Request $request)
    {
        $organizacion_id = session('organizacion_id');

        if (!$organizacion_id) {
            return redirect()->route('organizacion.login.form')->withErrors(['msg' => 'No hay sesión activa.']);
        }

        $org = Organizacion::find($organizacion_id);

        if ($org) {
            $org->clave = null; // se elimina la contraseña
            $org->save();
            session()->forget('organizacion_id'); // cerrar sesión
        }

        return redirect()->route('organizacion.login.form')
            ->with('cerrado', 'La inscripción ha sido cerrada. Si necesita abrir otro periodo, contacte al administrador de la Municipalidad.');

    }


    public function showLoginForm()
    {
        return view('organizaciones.login-organizacion');
    }




    public function tramo()
    {
        return $this->belongsTo(TramoEdad::class, 'tramo_id');
    }

}
