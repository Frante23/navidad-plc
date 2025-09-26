<?php

namespace App\Http\Controllers;

use App\Models\Beneficiario;
use App\Models\Formulario;
use App\Models\Organizacion;
use App\Models\Periodo; 
use App\Models\TramoEdad;
use App\Models\TipoOrganizacion;  
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\FormulariosExport;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Validation\Rule;

class OrganizacionController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'personalidad_juridica' => 'required|string',
            'clave' => 'required',
        ]);

        $org = \App\Models\Organizacion::where('personalidad_juridica', $request->personalidad_juridica)->first();

        if (!$org || !\Illuminate\Support\Facades\Hash::check($request->clave, $org->clave)) {
            return back()->withErrors(['personalidad_juridica' => 'Credenciales incorrectas'])->withInput();
        }

        if ($org->estado !== 'activo') {
            $msg = $org->estado === 'pendiente'
                ? 'Tu organización aún está en revisión (pendiente). Un funcionario debe habilitarla.'
                : 'Tu organización está inactiva. Contacta a la Municipalidad.';
            return back()->withErrors(['personalidad_juridica' => $msg])->withInput();
        }

        $periodoAbierto = \App\Models\Periodo::where('estado', 'abierto')->latest('anio')->first();
        if (!$periodoAbierto) {
            return back()->withErrors([
                'msg' => 'La inscripción está cerrada actualmente. Intente más tarde o contacte a la Municipalidad.'
            ])->withInput();
        }

        session(['organizacion_id' => $org->id]);

        return redirect()->route('panel.inicio');
    }




    public function logout(Request $request)
    {
        $request->session()->forget(['org.id','org.nombre']);
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('organizacion.login.form')
            ->with('cerrado', 'Sesión cerrada correctamente.');
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


    
    public function inicio(Request $request)
    {
        $organizacionId = $request->session()->get('organizacion_id');
        if (!$organizacionId) {
            return redirect('/')->with('status', 'Debes iniciar sesión.');
        }

        $organizacion = Organizacion::findOrFail($organizacionId);

        // 👇 Trae formularios + periodo y el conteo de beneficiarios
        $formularios = Formulario::with('periodo')
            ->withCount('beneficiarios')
            ->where('organizacion_id', $organizacionId)
            ->orderByDesc('id')
            ->paginate(10);

        // Para mostrar/ocultar el botón "Nuevo formulario"
        $periodoAbierto = Periodo::where('estado', 'abierto')->latest('anio')->first();

        return view('organizaciones.panel', compact('organizacion', 'formularios', 'periodoAbierto'));
    }


    public function showForm(Request $request)
    {
        $organizacionId = $request->session()->get('organizacion_id');
        if (!$organizacionId) {
            return redirect()->route('organizacion.login.form')->with('status', 'Debes iniciar sesión.');
        }

        $periodo = Periodo::where('estado', 'abierto')->latest('anio')->first();
        if (!$periodo) {
            return redirect()->route('panel.inicio')->with('status', 'No hay periodos abiertos actualmente.');
        }

        $formulario = Formulario::where('organizacion_id', $organizacionId)
            ->where('periodo_id', $periodo->id)
            ->where('estado', 'abierto')
            ->latest('created_at')
            ->first();

        if (!$formulario) {
            $formulario = Formulario::create([
                'organizacion_id' => $organizacionId,
                'estado'          => 'abierto',
                'periodo_id'      => $periodo->id,
            ]);
        }

                $beneficiarios = Beneficiario::where('formulario_id', $formulario->id)
            ->latest()
            ->get();

        $organizacion = Organizacion::find($organizacionId);
        return view('organizaciones.formulario', compact('beneficiarios', 'formulario', 'organizacion'));
    }




    public function verFormulario($id)
    {
        $organizacionId = session('organizacion_id');
        if (!$organizacionId) {
            return redirect('/')->with('status', 'Debes iniciar sesión.');
        }

        $formulario = Formulario::with('periodo')
            ->withCount('beneficiarios')
            ->findOrFail($id);

        if ($formulario->organizacion_id !== $organizacionId) {
            abort(403, 'No autorizado');
        }

        $beneficiarios = Beneficiario::where('formulario_id', $formulario->id)
            ->orderBy('nombre_completo')
            ->paginate(15);

        $organizacion   = Organizacion::find($organizacionId);
        $periodoAbierto = Periodo::where('estado','abierto')->latest('anio')->first();

        return view('organizaciones.formulario-show', compact(
            'formulario','beneficiarios','organizacion','periodoAbierto'
        ));
    }





    public function storeBeneficiario(Request $request)
    {
        $rutLimpio = $this->cleanRut($request->rut);
        if (!$this->validateRut($rutLimpio)) {
            return back()->withErrors(['rut' => "RUT inválido: $rutLimpio"])->withInput();
        }

        $request->validate([
            'nombre_completo'  => 'required|string',
            'fecha_nacimiento' => 'required|date',
            'sexo'             => 'nullable|in:M,F,U',
            'formulario_id'    => 'required|exists:formularios,id',
            'direccion'        => 'required|string',
        ]);

        $form = \App\Models\Formulario::with('periodo')->findOrFail($request->formulario_id);
        $anioPeriodo = $form->periodo?->anio ?? now()->year;
        $corte = \Carbon\Carbon::create($anioPeriodo, 12, 31, 23, 59, 59);

        $edadMeses = $this->edadEnMesesEnterosAlCorte($request->fecha_nacimiento, $corte);

        $isBaby = $edadMeses < 12;

        if (!$isBaby && $request->sexo === 'U') {
            return back()->withErrors([
                'sexo' => 'Para mayores de 11 meses al 31/12 del período, seleccione Masculino o Femenino.'
            ])->withInput();
        }
        $sexo = $isBaby ? 'U' : ($request->sexo ?: null);

        $tramo = \App\Models\TramoEdad::where('edad_min_meses', '<=', $edadMeses)
            ->where('edad_max_meses', '>=', $edadMeses)
            ->first();

        if (!$tramo) {
            return back()->withErrors([
                'edad' => "No existe un tramo de edad para este beneficiario (edad {$edadMeses} meses al 31/12/{$anioPeriodo})."
            ])->withInput();
        }

        \App\Models\Beneficiario::create([
            'rut'              => $rutLimpio,
            'nombre_completo'  => $request->nombre_completo,
            'fecha_nacimiento' => $request->fecha_nacimiento,
            'sexo'             => $sexo,
            'direccion'        => $request->direccion,
            'formulario_id'    => $request->formulario_id,
            'organizacion_id'  => session('organizacion_id'),
            'tramo_id'         => $tramo->id,
        ]);

        return redirect()->route('formulario.show')
            ->with('success_ben', 'Beneficiario registrado correctamente.');
    }


    public function update(Request $request, $id)
    {
        $beneficiario = \App\Models\Beneficiario::findOrFail($id);
        if ($beneficiario->organizacion_id !== session('organizacion_id')) {
            abort(403, 'No autorizado');
        }

        $rutLimpio = $this->cleanRut($request->input('rut', ''));
        if (!$this->validateRut($rutLimpio)) {
            return back()->withErrors(['rut' => "RUT inválido: $rutLimpio"])->withInput();
        }

        $request->validate([
            'nombre_completo'  => 'required|string',
            'fecha_nacimiento' => 'required|date',
            'sexo'             => 'nullable|in:M,F,U',
            'direccion'        => 'required|string',
        ]);

        $form = \App\Models\Formulario::with('periodo')->findOrFail($beneficiario->formulario_id);
        $anioPeriodo = $form->periodo?->anio ?? now()->year;
        $corte = \Carbon\Carbon::create($anioPeriodo, 12, 31, 23, 59, 59);

        $edadMeses = $this->edadEnMesesEnterosAlCorte($request->fecha_nacimiento, $corte);
        $isBaby    = $edadMeses < 12;

        if (!$isBaby && $request->sexo === 'U') {
            return back()->withErrors([
                'sexo' => 'Para mayores de 11 meses al 31/12 del período, seleccione Masculino o Femenino.'
            ])->withInput();
        }
        $sexo = $isBaby ? 'U' : ($request->sexo ?: null);

        $tramo = \App\Models\TramoEdad::where('edad_min_meses', '<=', $edadMeses)
            ->where('edad_max_meses', '>=', $edadMeses)
            ->first();

        if (!$tramo) {
            return back()->withErrors([
                'edad' => "No existe un tramo de edad para este beneficiario (edad {$edadMeses} meses al 31/12/{$anioPeriodo})."
            ])->withInput();
        }

        $beneficiario->rut              = $rutLimpio;
        $beneficiario->nombre_completo  = $request->nombre_completo;
        $beneficiario->fecha_nacimiento = $request->fecha_nacimiento;
        $beneficiario->sexo             = $sexo;
        $beneficiario->direccion        = $request->direccion;
        $beneficiario->tramo_id         = $tramo->id;
        $beneficiario->save();

        return redirect()
            ->route('formularios.show', $beneficiario->formulario_id)
            ->with('success_ben', 'Beneficiario actualizado correctamente.');
    }






    private function edadEnMesesEnteros(string $fecha): int
    {
        $fn  = \Carbon\Carbon::parse($fecha)->startOfDay();
        $now = \Carbon\Carbon::now()->startOfDay();

        $iv = $fn->diff($now); 
        return ($iv->y * 12) + $iv->m;
    }


    private function edadEnMesesEnterosAlCorte(string $fechaNac, \Carbon\Carbon $corte): int
    {
        $fn   = \Carbon\Carbon::parse($fechaNac)->startOfDay();
        $corte = $corte->copy()->startOfDay();

        if ($fn->greaterThan($corte)) {
            return 0; 
        }

        $iv = $fn->diff($corte); 
        return ($iv->y * 12) + $iv->m;
    }


  public function cerrar(Request $request)
    {
        $organizacionId = $request->session()->get('organizacion_id');
        if (!$organizacionId) {
            return redirect()->route('organizacion.login.form')->with('status', 'Debes iniciar sesión.');
        }

        Formulario::where('organizacion_id', $organizacionId)
            ->where('estado', 'abierto')
            ->update(['estado' => 'cerrado']);

        return redirect()->route('panel.inicio')
            ->with('cerrado', 'Formulario Cerrado. No puede agregar nuevos beneficiarios.');
    }


    public function showRegisterForm()
    {
        $tipos = TipoOrganizacion::orderBy('nombre')->get();
        return view('organizaciones.register', compact('tipos'));
    }


    public function register(\Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'tipo_organizacion_id'  => ['required','exists:tipos_organizaciones,id'],
            'nombre'                => ['required','string','max:255'],
            'personalidad_juridica' => ['required','string','max:100','unique:organizaciones,personalidad_juridica'],
            'domicilio_despacho'    => ['nullable','string','max:255'],
            'email'                 => ['nullable','email','max:255','unique:organizaciones,email'],
            'nombre_representante'  => ['nullable','string','max:255'],
            'telefono_contacto'     => ['nullable','string','max:50'],
            'observacion'           => ['nullable','string'],
            'fecha_creacion'        => ['nullable','date'],
        ]);

        $org = new Organizacion();
        $org->fill($data);
        $org->estado = 'pendiente';
        $org->clave  = null;    
        $org->save();

        return redirect()->route('organizacion.register.form')
            ->with('status', 'Registro enviado. Un funcionario municipal revisará y habilitará tu acceso.');
    }



    public function showLoginForm()
    {
        if (session()->has('org.id')) {
            return redirect()->route('panel.inicio');
        }
        return view('organizaciones.login-organizacion');
    }




    public function tramo()
    {
        return $this->belongsTo(TramoEdad::class, 'tramo_id');
    }





    public function edit($id)
    {
        $beneficiario = Beneficiario::findOrFail($id);

        if ($beneficiario->organizacion_id !== session('organizacion_id')) {
            abort(403, 'No autorizado');
        }

        $from = request('from', 'show');

        return view('organizaciones.edit-beneficiario', compact('beneficiario','from'));
    }




    public function destroy($id)
    {
        $beneficiario = Beneficiario::findOrFail($id);

        if ($beneficiario->organizacion_id !== session('organizacion_id')) {
            abort(403, 'No autorizado');
        }

        $beneficiario->delete();

        return redirect()->route('formulario.show')->with('success_ben', 'Beneficiario eliminado correctamente.');
    }








    public function descargar(Request $request)
    {
        $organizacionId = $request->session()->get('organizacion_id');
        if (!$organizacionId) {
            return redirect()->route('organizacion.login.form')->with('status', 'Debes iniciar sesión.');
        }

        $organizacion = Organizacion::findOrFail($organizacionId);

        // Trae formularios con periodo y beneficiarios
        $formularios = Formulario::with(['periodo', 'beneficiarios'])
            ->where('organizacion_id', $organizacionId)
            ->orderByDesc('id')
            ->get();

        // Stream CSV (UTF-8 con BOM para que Excel muestre bien tildes)
        $filename = 'formularios_'.$organizacion->id.'_'.now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function () use ($formularios, $organizacion) {
            $out = fopen('php://output', 'w');

            // BOM
            fwrite($out, "\xEF\xBB\xBF");

            // Encabezados
            fputcsv($out, [
                'Organización',
                'Formulario ID',
                'Formulario Estado',
                'Formulario Creado',
                'Periodo Año',
                'Periodo Estado',
                'Beneficiario ID',
                'RUT',
                'Nombre Completo',
                'Fecha Nacimiento',
                'Sexo',
                'Dirección',
                'Tramo ID',
                'Beneficiario Creado',
            ]);

            foreach ($formularios as $form) {
                if ($form->beneficiarios->isEmpty()) {
                    // fila “sin beneficiarios” (útil para tener registro del formulario)
                    fputcsv($out, [
                        $organizacion->nombre ?? $organizacion->id,
                        $form->id,
                        $form->estado,
                        optional($form->created_at)->format('Y-m-d H:i'),
                        optional($form->periodo)->anio,
                        optional($form->periodo)->estado,
                        '', '', '', '', '', '', '', // campos de beneficiario vacíos
                    ]);
                    continue;
                }

                foreach ($form->beneficiarios as $b) {
                    fputcsv($out, [
                        $organizacion->nombre ?? $organizacion->id,
                        $form->id,
                        $form->estado,
                        optional($form->created_at)->format('Y-m-d H:i'),
                        optional($form->periodo)->anio,
                        optional($form->periodo)->estado,
                        $b->id,
                        $b->rut,
                        $b->nombre_completo,
                        $b->fecha_nacimiento,
                        $b->sexo,
                        $b->direccion,
                        $b->tramo_id,
                        optional($b->created_at)->format('Y-m-d H:i'),
                    ]);
                }
            }

            fclose($out);
        }, 200, $headers);
    }

    
    private function buildExportRows(int $organizacionId): \Illuminate\Support\Collection
    {
        $organizacion = Organizacion::findOrFail($organizacionId);

        $formularios = Formulario::with(['periodo','beneficiarios'])
            ->where('organizacion_id', $organizacionId)
            ->orderByDesc('id')
            ->get();

        $rows = collect();
        foreach ($formularios as $form) {
            if ($form->beneficiarios->isEmpty()) {
                $rows->push([
                    $organizacion->nombre ?? $organizacion->id,
                    $form->id, $form->estado, optional($form->created_at)->format('Y-m-d H:i'),
                    optional($form->periodo)->anio, optional($form->periodo)->estado,
                    '', '', '', '', '', '', '',
                ]);
            } else {
                foreach ($form->beneficiarios as $b) {
                    $rows->push([
                        $organizacion->nombre ?? $organizacion->id,
                        $form->id, $form->estado, optional($form->created_at)->format('Y-m-d H:i'),
                        optional($form->periodo)->anio, optional($form->periodo)->estado,
                        $b->id, $b->rut, $b->nombre_completo, $b->fecha_nacimiento, $b->sexo, $b->direccion, $b->tramo_id,
                    ]);
                }
            }
        }
        return $rows;
    }

    public function exportXlsx(Request $request)
    {
        $orgId = $request->session()->get('organizacion_id');
        if (!$orgId) return redirect()->route('organizacion.login.form');

        $rows = $this->buildExportRows($orgId);
        $filename = 'formularios_'.$orgId.'_'.now()->format('Ymd_His').'.xlsx';

        return Excel::download(new FormulariosExport($rows), $filename);
    }







    public function exportPdf(Request $request)
    {
        $orgId = $request->session()->get('organizacion_id');
        if (!$orgId) return redirect()->route('organizacion.login.form');

        $organizacion = Organizacion::findOrFail($orgId);
        $formularios = Formulario::with(['periodo','beneficiarios'])
            ->where('organizacion_id', $orgId)
            ->orderByDesc('id')
            ->get();

        $html = view('organizaciones.exports.formularios-pdf', compact('organizacion','formularios'))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans'); // soporte UTF-8

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'formularios_'.$orgId.'_'.now()->format('Ymd_His').'.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }




}



