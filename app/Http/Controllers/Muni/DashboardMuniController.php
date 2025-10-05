<?php

namespace App\Http\Controllers\Muni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Organizacion;
use App\Models\Formulario;
use App\Models\Beneficiario;
use App\Models\Periodo;
use App\Models\TramoEdad;
use App\Models\TipoOrganizacion;
use Illuminate\Support\Facades\DB;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\Hash;  
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NuevaClaveOrgMail;



class DashboardMuniController extends Controller
{
    public function index(Request $request)
    {
        $funcionario = auth('func')->user();

        $q          = trim($request->get('q', ''));
        $periodoSel = $request->integer('periodo_id');
        $estadoSel  = $request->get('estado'); 
        $sort       = $request->get('sort', 'nombre');
        $direction  = strtolower($request->get('direction', 'asc')) === 'desc' ? 'desc' : 'asc';

        $periodos = Periodo::orderByDesc('anio')->get();
        $tramos   = TramoEdad::orderBy('edad_min_meses')->get();

        $orgsQuery = Organizacion::query()
            ->select('organizaciones.*')
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('nombre', 'like', "%{$q}%")
                    ->orWhere('personalidad_juridica', 'like', "%{$q}%");
                });
            })
            ->when(isset($estadoSel) && $estadoSel !== '', function ($qq) use ($estadoSel) {
                $qq->where('organizaciones.estado', $estadoSel);
            });

        $sortableNative = ['nombre', 'personalidad_juridica', 'estado', 'created_at', 'updated_at'];

        if (in_array($sort, $sortableNative, true)) {
            $orgsQuery->orderBy($sort, $direction);
        }

        elseif ($sort === 'beneficiarios') {
            $subBen = \DB::table('beneficiarios as b')
                ->selectRaw('b.organizacion_id, COUNT(*) as ben_count')
                ->join('formularios as f', 'f.id', '=', 'b.formulario_id')
                ->when($periodoSel, fn($qq) => $qq->where('f.periodo_id', $periodoSel))
                ->groupBy('b.organizacion_id');

            $orgsQuery
                ->leftJoinSub($subBen, 'benc', function ($join) {
                    $join->on('organizaciones.id', '=', 'benc.organizacion_id');
                })
                ->orderByRaw('COALESCE(benc.ben_count, 0) ' . $direction);
        }

        elseif ($sort === 'formularios') {
            $subForm = \DB::table('formularios as f')
                ->selectRaw('f.organizacion_id, COUNT(*) as form_count')
                ->when($periodoSel, fn($qq) => $qq->where('f.periodo_id', $periodoSel))
                ->groupBy('f.organizacion_id');

            $orgsQuery
                ->leftJoinSub($subForm, 'formc', function ($join) {
                    $join->on('organizaciones.id', '=', 'formc.organizacion_id');
                })
                ->orderByRaw('COALESCE(formc.form_count, 0) ' . $direction);
        }

        else {
            $orgsQuery->orderBy('nombre', 'asc');
        }

        $organizaciones = $orgsQuery->paginate(10)->withQueryString();

        $orgIds = $organizaciones->pluck('id');

        $formStats = Formulario::selectRaw('organizacion_id, estado, COUNT(*) c')
            ->when($periodoSel, fn($qq)=>$qq->where('periodo_id', $periodoSel))
            ->whereIn('organizacion_id', $orgIds)
            ->groupBy('organizacion_id','estado')
            ->get()
            ->groupBy('organizacion_id');

        $benByTramo = Beneficiario::selectRaw('beneficiarios.organizacion_id, beneficiarios.tramo_id, COUNT(*) c')
            ->join('formularios as f','f.id','=','beneficiarios.formulario_id')
            ->when($periodoSel, fn($qq)=>$qq->where('f.periodo_id', $periodoSel))
            ->whereIn('beneficiarios.organizacion_id', $orgIds)
            ->groupBy('beneficiarios.organizacion_id','beneficiarios.tramo_id')
            ->get()
            ->groupBy('organizacion_id');

        $benTotals = Beneficiario::selectRaw('beneficiarios.organizacion_id, COUNT(*) c')
            ->join('formularios as f','f.id','=','beneficiarios.formulario_id')
            ->when($periodoSel, fn($qq)=>$qq->where('f.periodo_id', $periodoSel))
            ->whereIn('beneficiarios.organizacion_id', $orgIds)
            ->groupBy('beneficiarios.organizacion_id')
            ->pluck('c','organizacion_id');

        return view('municipales.dashboard', compact(
            'funcionario','organizaciones','periodos','periodoSel','tramos',
            'formStats','benByTramo','benTotals','q','estadoSel','sort','direction'
        ));
    }


    public function showOrg(Request $request, int $id)
    {
        $funcionario = auth('func')->user();
        $org = \App\Models\Organizacion::findOrFail($id);

        $periodos   = \App\Models\Periodo::orderBy('anio','desc')->get();
        $periodoSel = $request->integer('periodo_id');

        $formularios = \App\Models\Formulario::with('periodo')
            ->withCount('beneficiarios')
            ->where('organizacion_id', $org->id)
            ->when($periodoSel, fn($q) => $q->where('periodo_id', $periodoSel))
            ->orderByDesc('id')
            ->paginate(10)
            ->appends($request->query());

        $beneficiarios = \App\Models\Beneficiario::where('organizacion_id', $org->id)
            ->when($periodoSel, function($q) use ($periodoSel) {
                $q->whereHas('formulario', fn($qq) => $qq->where('periodo_id', $periodoSel));
            })
            ->orderByDesc('id')
            ->paginate(15)
            ->appends($request->query());

        return view('municipales.org-show', compact(
            'funcionario', 'org', 'periodos', 'periodoSel', 'formularios', 'beneficiarios'
        ));
    }



    public function exportCsv(Request $request)
    {
        $periodoSel = $request->integer('periodo_id');
        $q          = trim($request->get('q', ''));

        $orgIds = Organizacion::when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('nombre', 'like', "%{$q}%")
                      ->orWhere('personalidad_juridica', 'like', "%{$q}%");
                });
            })->pluck('id');

        $rows = DB::table('beneficiarios as b')
            ->join('formularios as f', 'f.id','=','b.formulario_id')
            ->join('organizaciones as o', 'o.id','=','b.organizacion_id')
            ->leftJoin('tramos_edad as t', 't.id','=','b.tramo_id')
            ->leftJoin('periodos as p', 'p.id','=','f.periodo_id')
            ->when($periodoSel, fn($qq)=>$qq->where('f.periodo_id', $periodoSel))
            ->whereIn('b.organizacion_id', $orgIds)
            ->selectRaw("
                o.nombre AS organizacion,
                o.personalidad_juridica AS pj,
                f.id AS formulario_id,
                f.estado AS formulario_estado,
                p.anio AS periodo_anio,
                b.id AS beneficiario_id,
                b.rut,
                b.nombre_completo,
                b.fecha_nacimiento,
                b.sexo,
                b.direccion,
                b.rut_jefe_hogar,
                b.porcentaje_rsh,
                b.aceptado,
                b.observaciones,
                t.nombre_tramo,
                b.created_at AS beneficiario_creado
            ")
            ->orderBy('o.nombre')->orderBy('f.id')->orderBy('b.id')
            ->get();

        $filename = 'muni_export_'.now()->format('Ymd_His').'.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        return response()->stream(function () use ($rows) {
            $out = fopen('php://output','w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, [
                'Organización','PJ','FormularioID','FormularioEstado','Periodo',
                'BeneficiarioID','RUT','Nombre','FechaNacimiento','Sexo','Dirección','Tramo','Creado'
            ]);
            foreach ($rows as $r) {
                fputcsv($out, [
                    $r->organizacion, $r->pj, $r->formulario_id, $r->formulario_estado, $r->periodo_anio,
                    $r->beneficiario_id, $r->rut, $r->nombre_completo, $r->fecha_nacimiento, $r->sexo, $r->direccion,
                    $r->nombre_tramo, $r->beneficiario_creado,
                ]);
            }
            fclose($out);
        }, 200, $headers);
    }








    private function buildRows(?int $periodoId, ?string $q)
    {
        $orgIds = Organizacion::when($q, function ($qq) use ($q) {
            $qq->where(function ($w) use ($q) {
                $w->where('nombre', 'like', "%{$q}%")
                ->orWhere('personalidad_juridica', 'like', "%{$q}%");
            });
        })->pluck('id');

        return DB::table('beneficiarios as b')
            ->join('formularios as f', 'f.id','=','b.formulario_id')
            ->join('organizaciones as o', 'o.id','=','b.organizacion_id')
            ->leftJoin('tramos_edad as t', 't.id','=','b.tramo_id') 
            ->leftJoin('periodos as p', 'p.id','=','f.periodo_id')
            ->when($periodoId, fn($qq)=>$qq->where('f.periodo_id', $periodoId))
            ->whereIn('b.organizacion_id', $orgIds)
            ->select([
                'o.nombre                 as organizacion',
                'o.personalidad_juridica  as pj',
                'f.id                     as formulario_id',
                'f.estado                 as formulario_estado',
                'p.anio                   as periodo_anio',
                'b.id                     as beneficiario_id',
                'b.rut',
                'b.nombre_completo',
                'b.fecha_nacimiento',
                'b.sexo',
                'b.direccion',
                't.nombre_tramo           as nombre_tramo',  
                'b.created_at             as beneficiario_creado',
                'b.rut_jefe_hogar',
                'b.porcentaje_rsh',
                'b.observaciones',
                'b.aceptado',
            ])
            ->orderBy('o.nombre')->orderBy('f.id')->orderBy('b.id')
            ->get();
    }



    private function buildOrgRows(int $orgId, ?int $periodoId)
    {
        return DB::table('beneficiarios as b')
            ->join('formularios as f', 'f.id','=','b.formulario_id')
            ->join('organizaciones as o', 'o.id','=','b.organizacion_id')
            ->leftJoin('tramos_edad as t', 't.id','=','b.tramo_id')   
            ->leftJoin('periodos as p', 'p.id','=','f.periodo_id')
            ->where('b.organizacion_id', $orgId)
            ->when($periodoId, fn($qq)=>$qq->where('f.periodo_id', $periodoId))
            ->select([
                'o.nombre                 as organizacion',
                'o.personalidad_juridica  as pj',
                'f.id                     as formulario_id',
                'f.estado                 as formulario_estado',
                'p.anio                   as periodo_anio',
                'b.id                     as beneficiario_id',
                'b.rut',
                'b.nombre_completo',
                'b.fecha_nacimiento',
                'b.sexo',
                'b.direccion',
                't.nombre_tramo           as nombre_tramo',  
                'b.created_at             as beneficiario_creado',
                'b.rut_jefe_hogar',
                'b.porcentaje_rsh',
                'b.observaciones',
                'b.aceptado',
            ])
            ->orderBy('f.id')->orderBy('b.id')
            ->get();
    }




    public function exportXlsx(Request $request)
    {
        $rows = $this->buildRows($request->integer('periodo_id'), trim($request->get('q','')));

        $filename = 'muni_export_'.now()->format('Ymd_His').'.xls';
        $headers = [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $html = view('municipales.exports.table-xls', [
            'rows'   => $rows,
            'titulo' => 'Exportación agrupada',
        ])->render();

        return response($html, 200, $headers);
    }


    public function exportPdf(Request $request)
    {
        $rows = $this->buildRows($request->integer('periodo_id'), trim($request->get('q','')));

        $html = view('municipales.exports.table-pdf', [
            'rows'   => $rows,
            'titulo' => 'Exportación agrupada'
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'muni_export_'.now()->format('Ymd_His').'.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }



        public function exportOrgXlsx($id, Request $request)
        {
            $rows = $this->buildOrgRows((int)$id, $request->integer('periodo_id'));
            $org  = \App\Models\Organizacion::findOrFail($id);

            $filename = 'org_'.$id.'_export_'.now()->format('Ymd_His').'.xls';
            $headers = [
                'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $html = view('municipales.exports.table-xls', [
                'rows'   => $rows,
                'titulo' => 'Organización: '.$org->nombre.' ('.$org->personalidad_juridica.')',
            ])->render();

            return response($html, 200, $headers);
        }


        public function exportOrgPdf($id, Request $request)
        {
            $rows = $this->buildOrgRows((int)$id, $request->integer('periodo_id'));
            $org  = Organizacion::findOrFail($id);

            $html = view('municipales.exports.table-pdf', [
                'rows'   => $rows,
                'titulo' => 'Organización: '.$org->nombre.' ('.$org->personalidad_juridica.')'
            ])->render();

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            $filename = 'org_'.$id.'_export_'.now()->format('Ymd_His').'.pdf';
            return response($dompdf->output(), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ]);
        }




        private function buildFormRows(int $formId)
        {
            return DB::table('beneficiarios as b')
                ->join('formularios as f', 'f.id','=','b.formulario_id')
                ->join('organizaciones as o', 'o.id','=','b.organizacion_id')
                ->leftJoin('tramos_edad as t', 't.id','=','b.tramo_id')
                ->leftJoin('periodos as p', 'p.id','=','f.periodo_id')
                ->where('b.formulario_id', $formId)
                ->selectRaw("
                    o.nombre AS organizacion,
                    o.personalidad_juridica AS pj,
                    f.id AS formulario_id,
                    f.estado AS formulario_estado,
                    p.anio AS periodo_anio,
                    b.id AS beneficiario_id,
                    b.rut,
                    b.nombre_completo,
                    b.fecha_nacimiento,
                    b.sexo,
                    b.direccion,
                    t.nombre_tramo,
                    b.created_at AS beneficiario_creado
                ")
                ->orderBy('b.id')
                ->get();
        }

        public function exportFormXlsx(int $id)
        {
            $form = \App\Models\Formulario::with('periodo','organizacion')->findOrFail($id);

            $rows = DB::table('beneficiarios as b')
                ->join('formularios as f', 'f.id', '=', 'b.formulario_id')
                ->join('organizaciones as o', 'o.id', '=', 'b.organizacion_id')
                ->leftJoin('tramos_edad as t', 't.id', '=', 'b.tramo_id')
                ->where('b.formulario_id', $id)
                ->select([
                    'o.nombre                 as organizacion',
                    'o.personalidad_juridica  as pj',
                    'f.id                     as formulario_id',
                    'f.estado                 as formulario_estado',
                    'f.periodo_id',
                    'b.id                     as beneficiario_id',   
                    'b.rut',
                    'b.nombre_completo',
                    'b.fecha_nacimiento',
                    'b.sexo',
                    'b.direccion',
                    't.nombre_tramo           as nombre_tramo',
                    'b.created_at             as beneficiario_creado',
                    'b.rut_jefe_hogar',
                    'b.porcentaje_rsh',
                    'b.observaciones',
                    'b.aceptado',
                ])
                ->orderBy('b.id')
                ->get()
                ->map(function ($r) use ($form) {
                    $r->periodo_anio = $form->periodo?->anio;
                    return $r;
                });

            return response()->view('municipales.exports.table-xls', [
                'rows'   => $rows,
                'titulo' => 'Formulario #'.$form->id.' – '.$form->organizacion?->nombre,
            ])->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="formulario_'.$form->id.'.xls"');
        }







        public function exportFormPdf(int $id)
        {
            $form = \App\Models\Formulario::with('periodo','organizacion')->findOrFail($id);

            $rows = DB::table('beneficiarios as b')
                ->join('formularios as f', 'f.id', '=', 'b.formulario_id')
                ->join('organizaciones as o', 'o.id', '=', 'b.organizacion_id')
                ->leftJoin('tramos_edad as t', 't.id', '=', 'b.tramo_id')
                ->where('b.formulario_id', $id)
                ->select([
                    'o.nombre                 as organizacion',
                    'o.personalidad_juridica  as pj',
                    'f.id                     as formulario_id',
                    'f.periodo_id',
                    'b.rut',
                    'b.nombre_completo',
                    'b.fecha_nacimiento',
                    'b.sexo',
                    'b.direccion',
                    't.nombre_tramo           as nombre_tramo',   
                    'b.created_at             as beneficiario_creado',
                    'b.rut_jefe_hogar',
                    'b.porcentaje_rsh',
                    'b.observaciones',
                    'b.aceptado',
                ])
                ->orderBy('b.id')
                ->get()
                ->map(function ($r) use ($form) {
                    $r->periodo_anio = $form->periodo?->anio;
                    return $r;
                });

            $options = new \Dompdf\Options();
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');

            $dompdf = new \Dompdf\Dompdf($options);
            $html   = view('municipales.exports.table-pdf', [
                'titulo' => 'Formulario #'.$form->id.' – '.$form->organizacion?->nombre,
                'rows'   => $rows,
            ])->render();

            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4', 'landscape');
            $dompdf->render();

            return response($dompdf->output(), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="formulario_'.$form->id.'.pdf"',
            ]);
        }






    public function createOrg()
    {
        $funcionario = auth('func')->user();
        $tipos = TipoOrganizacion::orderBy('nombre')->get();  
        return view('municipales.org-create', compact('funcionario','tipos'));
    }

    public function storeOrg(Request $request)
    {
        $data = $request->validate([
            'tipo_organizacion_id'   => 'required|exists:tipos_organizaciones,id',
            'nombre'                => 'required|string|max:255',
            'personalidad_juridica' => 'required|string|max:100',
            'domicilio_despacho'    => 'required|string|max:255',
            'email'                 => 'nullable|email|max:255',
            'nombre_representante'  => 'required|string|max:255',
            'telefono_contacto'     => 'nullable|string|max:50',
            'observacion'           => 'nullable|string',
            'clave'                 => 'required|string|min:6',
            'estado'                => 'nullable|in:activo,inactivo',   
            'fecha_creacion'        => 'required|date',                
        ]);

        $data['estado'] = $data['estado'] ?? 'activo';

        $data['clave'] = Hash::make($data['clave']);

        \App\Models\Organizacion::create($data);

        return redirect()->route('muni.dashboard')
            ->with('status', 'Agrupación creada correctamente.');
    }







    public function orgPendientes()
    {
        $funcionario = auth('func')->user();

        $pendientes = Organizacion::where('estado','pendiente')
            ->orderBy('created_at','desc')->paginate(15);

        $inactivas = Organizacion::where('estado','inactivo')
            ->orderBy('updated_at','desc')->paginate(15, ['*'], 'inactivas_page');

        return view('municipales.org-pendientes', compact('funcionario','pendientes','inactivas'));
    }


    public function orgAprobar(Request $request, $id)
    {
        $request->validate([
            'clave' => ['required','string','min:6','confirmed'],
        ]);

        $org = Organizacion::findOrFail($id);
        if ($org->estado !== 'pendiente') {
            return back()->with('status','La organización ya no está en estado pendiente.');
        }

        $org->estado = 'activo';
        $org->clave  = Hash::make($request->clave);
        $org->save();

        Mail::to($org->email)->send(new NuevaClaveOrgMail($org, $request->clave));

        return back()->with('status','Organización aprobada, activada y correo enviado.');
    }





    public function orgRechazar($id)
    {
        $org = Organizacion::findOrFail($id);

        $org->estado = 'inactivo';
        $org->clave  = null;
        $org->save();

        return back()->with('status','Organización movida a inactivas.');
    }

    


    public function orgDesactivar($id)
    {
        $org = Organizacion::findOrFail($id);

        $org->estado = 'inactivo';
        $org->clave  = null;
        $org->save();

        return back()->with('status', 'Organización desactivada y contraseña eliminada.');
    }

    public function orgReactivar($id, \Illuminate\Http\Request $request)
    {
        $data = $request->validate([
            'clave' => ['required','string','min:6','confirmed'],
        ]);

        $org = Organizacion::findOrFail($id);

        $plain = $data['clave'];
        $org->estado = 'activo';
        $org->clave  = \Illuminate\Support\Facades\Hash::make($plain);
        $org->save();

        Mail::to($org->email)->send(new NuevaClaveOrgMail($org, $request->clave));

        return back()->with('status', 'Organización reactivada; correo enviado con la nueva contraseña.');
    }



    public function orgActivarInactiva(Request $request, $id)
    {
        $request->validate([
            'clave' => ['required','string','min:6','confirmed'],
        ]);

        $org = Organizacion::findOrFail($id);
        if ($org->estado !== 'inactivo') {
            return back()->with('status','La organización no está inactiva.');
        }

        $plain = $request->clave;
        $org->estado = 'activo';
        $org->clave  = \Illuminate\Support\Facades\Hash::make($plain);
        $org->save();

        Mail::to($org->email)->send(new NuevaClaveOrgMail($org, $request->clave));

        return back()->with('status','Organización reactivada; correo enviado con la nueva contraseña.');
    }



    public function duplicados(Request $request)
    {
        $funcionario = auth('func')->user();
        $periodoSel  = $request->integer('periodo_id');

        $periodos = \App\Models\Periodo::orderByDesc('anio')->get();

        $agrupado = DB::table('intentos_duplicados as i')
            ->when($periodoSel, fn($q)=>$q->where('i.periodo_id', $periodoSel))
            ->selectRaw('i.rut, COUNT(*) as intentos, MAX(i.created_at) as ultimo_intento')
            ->groupBy('i.rut')
            ->orderByDesc('ultimo_intento')
            ->paginate(30)
            ->withQueryString();

        $rutDetalle = null;
        if ($request->filled('rut')) {
            $rut = $request->get('rut');
            $rutDetalle = DB::table('intentos_duplicados as i')
                ->leftJoin('organizaciones as o1','o1.id','=','i.organizacion_id')
                ->leftJoin('organizaciones as o2','o2.id','=','i.existe_en_org_id')
                ->leftJoin('formularios as f1','f1.id','=','i.formulario_id')
                ->leftJoin('formularios as f2','f2.id','=','i.existe_en_form_id')
                ->leftJoin('periodos as p','p.id','=','i.periodo_id')
                ->where('i.rut', $rut)
                ->when($periodoSel, fn($q)=>$q->where('i.periodo_id', $periodoSel))
                ->selectRaw("
                    i.id, i.created_at as intento_fecha, i.ip,
                    i.rut, p.anio as periodo,
                    o1.nombre as intento_org, i.formulario_id,
                    o2.nombre as existe_org, i.existe_en_form_id, i.existe_fecha
                ")
                ->orderByDesc('i.created_at')
                ->paginate(30, ['*'], 'detalle_page')
                ->withQueryString();
        }

        return view('municipales.duplicados', compact('funcionario','periodos','periodoSel','agrupado','rutDetalle'));
    }





    public function setOrgStatus($id, Request $request)
    {
        $request->validate([
            'estado' => 'required|in:activo,pendiente,inactivo',
        ]);

        $org = \App\Models\Organizacion::findOrFail($id);
        $org->estado = $request->estado;
        $org->save();

        return back()->with('status', "Estado de «{$org->nombre}» actualizado a {$org->estado}.");
    }





   public function reviewBeneficiario(Request $request, int $id)
    {
        $ben = \App\Models\Beneficiario::findOrFail($id);

        $pr = $request->input('porcentaje_rsh');
        if ($pr === '' || $pr === ' ') {
            $request->merge(['porcentaje_rsh' => null]);
        }

        $request->validate([
            'accion'         => ['required','in:aceptar,rechazar,guardar'],
            'porcentaje_rsh' => ['nullable','integer','between:0,100'],
            'observaciones'  => ['nullable','string'],
        ]);

        $accion = $request->string('accion')->toString();

        if ($accion === 'aceptar') {
            $ben->aceptado = 1;
        } elseif ($accion === 'rechazar') {
            $ben->aceptado = 0;
        } 

        if ($request->filled('porcentaje_rsh') || $request->has('porcentaje_rsh')) {
            $ben->porcentaje_rsh = $request->input('porcentaje_rsh'); 
        }
        if ($request->has('observaciones')) {
            $ben->observaciones = $request->input('observaciones');
        }

        $ben->save();

        return back()->with('status', 'Revisión actualizada.');
    }








    public function formBeneficiarios(\Illuminate\Http\Request $request, int $id)
    {
        $funcionario = auth('func')->user();

        $form = \App\Models\Formulario::with(['periodo','organizacion'])
            ->findOrFail($id);

        $beneficiarios = \App\Models\Beneficiario::where('formulario_id', $form->id)
            ->orderByDesc('id')
            ->paginate(20)
            ->appends($request->query());

        $backParams = ['id' => $form->organizacion_id] + $request->only('periodo_id');

        return view('municipales.form-beneficiarios', [
            'funcionario'    => $funcionario,
            'form'           => $form,
            'beneficiarios'  => $beneficiarios,
            'backParams'     => $backParams,
        ]);
    }





    private function buildOrgListado(?int $periodoId, ?string $q)
    {
        $subForm = DB::table('formularios as f')
            ->selectRaw('f.organizacion_id, COUNT(*) as form_count')
            ->when($periodoId, fn($qq) => $qq->where('f.periodo_id', $periodoId))
            ->groupBy('f.organizacion_id');

        $subBen = DB::table('beneficiarios as b')
            ->join('formularios as f', 'f.id', '=', 'b.formulario_id')
            ->selectRaw('b.organizacion_id, COUNT(*) as ben_count')
            ->when($periodoId, fn($qq) => $qq->where('f.periodo_id', $periodoId))
            ->groupBy('b.organizacion_id');

        return DB::table('organizaciones as o')
            ->leftJoinSub($subForm, 'formc', 'formc.organizacion_id', '=', 'o.id')
            ->leftJoinSub($subBen,  'benc',  'benc.organizacion_id',  '=', 'o.id')
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('o.nombre', 'like', "%{$q}%")
                    ->orWhere('o.personalidad_juridica', 'like', "%{$q}%");
                });
            })
            ->selectRaw("
                o.id,
                o.nombre,
                o.personalidad_juridica as pj,
                o.estado,
                o.created_at,
                COALESCE(formc.form_count, 0) as formularios,
                COALESCE(benc.ben_count, 0)  as beneficiarios
            ")
            ->orderBy('o.nombre', 'asc');
    }





    public function exportListadoOrganizacionesPdf(Request $request)
    {
        $periodoId = $request->integer('periodo_id');
        $q         = trim($request->get('q',''));

        $rows = $this->buildOrgListado($periodoId, $q)->get();

        $titulo = 'Listado de Organizaciones'.($periodoId ? " (Período {$periodoId})" : '');

        $html = view('municipales.exports.orgs-pdf', compact('rows','titulo'))->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape'); 
        $dompdf->render();

        $filename = 'organizaciones_'.now()->format('Ymd_His').'.pdf';
        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }


    

    public function estadisticas(Request $request)
    {
        $funcionario = auth('func')->user();

        $periodoId  = $request->integer('periodo_id');
        $orgId      = $request->integer('org_id'); 

        $totalOrgs = \App\Models\Organizacion::count();
        $activos   = \App\Models\Organizacion::where('estado','activo')->count();
        $pendientes= \App\Models\Organizacion::where('estado','pendiente')->count();
        $inactivos = \App\Models\Organizacion::where('estado','inactivo')->count();

        $formsAbiertos = \App\Models\Formulario::when($periodoId, fn($q)=>$q->where('periodo_id',$periodoId))
            ->where('estado','abierto')->count();
        $formsCerrados = \App\Models\Formulario::when($periodoId, fn($q)=>$q->where('periodo_id',$periodoId))
            ->where('estado','cerrado')->count();

        $topOrgs = DB::table('beneficiarios as b')
            ->join('formularios as f','f.id','=','b.formulario_id')
            ->join('organizaciones as o','o.id','=','b.organizacion_id')
            ->when($periodoId, fn($q)=>$q->where('f.periodo_id',$periodoId))
            ->select('o.id','o.nombre', DB::raw('COUNT(*) as total_beneficiarios'))
            ->groupBy('o.id','o.nombre')
            ->orderByDesc('total_beneficiarios')
            ->limit(10)
            ->get();

        $porTipo = DB::table('organizaciones as o')
            ->leftJoin('tipos_organizaciones as t','t.id','=','o.tipo_organizacion_id')
            ->select(DB::raw('COALESCE(t.nombre,"Sin tipo") as tipo'), DB::raw('COUNT(*) as c'))
            ->groupBy('tipo')
            ->orderBy('tipo')
            ->get();

        $chartEstadosRaw = DB::table('organizaciones')
            ->select('estado', DB::raw('COUNT(*) as c'))
            ->groupBy('estado')->get();
        $chartLabels = $chartEstadosRaw->pluck('estado');
        $chartData   = $chartEstadosRaw->pluck('c');

        $sexoGlobal = DB::table('beneficiarios as b')
            ->join('formularios as f','f.id','=','b.formulario_id')
            ->when($periodoId, fn($q)=>$q->where('f.periodo_id',$periodoId))
            ->when($orgId, fn($q)=>$q->where('b.organizacion_id',$orgId))
            ->select('b.sexo', DB::raw('COUNT(*) as c'))
            ->groupBy('b.sexo')->pluck('c','sexo')->all();

        $tramos = DB::table('tramos_edad')->orderBy('edad_min_meses')->get(['id','nombre_tramo']);
        $stackTramoLabels = $tramos->pluck('nombre_tramo');
        $stackM = array_fill(0, $tramos->count(), 0);
        $stackF = array_fill(0, $tramos->count(), 0);
        $stackU = array_fill(0, $tramos->count(), 0);

        $porTramoSexo = DB::table('beneficiarios as b')
            ->join('formularios as f','f.id','=','b.formulario_id')
            ->when($periodoId, fn($q)=>$q->where('f.periodo_id',$periodoId))
            ->when($orgId, fn($q)=>$q->where('b.organizacion_id',$orgId))
            ->select('b.tramo_id','b.sexo', DB::raw('COUNT(*) as c'))
            ->groupBy('b.tramo_id','b.sexo')->get();

        $indexByTramo = $tramos->pluck(null,'id'); 
        $posByTramoId = [];
        foreach ($tramos as $i => $t) $posByTramoId[$t->id] = $i;

        foreach ($porTramoSexo as $r) {
            if (!isset($posByTramoId[$r->tramo_id])) continue;
            $pos = $posByTramoId[$r->tramo_id];
            if ($r->sexo === 'M') $stackM[$pos] = (int)$r->c;
            elseif ($r->sexo === 'F') $stackF[$pos] = (int)$r->c;
            else $stackU[$pos] = (int)$r->c;
        }

        $anyo = optional(\App\Models\Periodo::find($periodoId))->anio ?? Carbon::now()->year;
        $porMes = DB::table('beneficiarios as b')
            ->join('formularios as f','f.id','=','b.formulario_id')
            ->when($periodoId, fn($q)=>$q->where('f.periodo_id',$periodoId))
            ->when(!$periodoId, fn($q)=>$q->whereYear('b.created_at',$anyo))
            ->when($orgId, fn($q)=>$q->where('b.organizacion_id',$orgId))
            ->select(DB::raw('MONTH(b.created_at) as m'), DB::raw('COUNT(*) as c'))
            ->groupBy(DB::raw('MONTH(b.created_at)'))
            ->pluck('c','m')->all();

        $mesLabels = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        $mesData   = [];
        for ($i=1;$i<=12;$i++){
            $mesData[] = (int)($porMes[$i] ?? 0);
        }

        $scatter = DB::table('beneficiarios as b')
            ->join('formularios as f','f.id','=','b.formulario_id')
            ->when($periodoId, fn($q)=>$q->where('f.periodo_id',$periodoId))
            ->when($orgId, fn($q)=>$q->where('b.organizacion_id',$orgId))
            ->whereNotNull('b.porcentaje_rsh')
            ->select('b.fecha_nacimiento','b.porcentaje_rsh')
            ->limit(1000) 
            ->get()
            ->map(function($r) use ($anyo){
                $fn = Carbon::parse($r->fecha_nacimiento)->startOfDay();
                $corte = Carbon::create($anyo,12,31,23,59,59);
                if ($fn->greaterThan($corte)) { $meses=0; }
                else {
                    $dif = $fn->diff($corte);
                    $meses = $dif->y*12 + $dif->m;
                }
                return ['x'=>$meses, 'y'=>(int)$r->porcentaje_rsh];
            });

        $orgsSelect = \App\Models\Organizacion::orderBy('nombre')->get(['id','nombre']);

        return view('municipales.estadisticas', compact(
            'funcionario',
            'periodoId','orgId',
            'totalOrgs','activos','pendientes','inactivos',
            'formsAbiertos','formsCerrados',
            'topOrgs','porTipo',
            'chartLabels','chartData',
            'sexoGlobal','stackTramoLabels','stackM','stackF','stackU',
            'mesLabels','mesData',
            'scatter',
            'orgsSelect'
        ));
    }







    public function bulkSaveBeneficiarios(Request $request, int $id)
    {
        $form = \App\Models\Formulario::findOrFail($id);

        $items = $request->input('items', []);
        if (!is_array($items) || empty($items)) {
            return back()->with('status', 'No hay cambios para guardar.');
        }

        foreach ($items as $benId => $payload) {
            $ben = \App\Models\Beneficiario::where('formulario_id', $form->id)->find($benId);
            if (!$ben) continue;

            $porc = $payload['porcentaje_rsh'] ?? null;
            if ($porc !== null && $porc !== '') {
                $porc = (int) $porc;
                if ($porc < 0)   $porc = 0;
                if ($porc > 100) $porc = 100;
            } else {
                $porc = null; 
            }

            $obs = $payload['observaciones'] ?? null;

            $ben->porcentaje_rsh = $porc;
            $ben->observaciones  = $obs;
            $ben->save();
        }

        return back()->with('status', 'Cambios guardados correctamente.');
    }



    private function buildOrgFormsSummary(int $orgId, ?int $periodoId = null)
    {
        return \DB::table('formularios as f')
            ->leftJoin('periodos as p', 'p.id', '=', 'f.periodo_id')
            ->leftJoin('beneficiarios as b', 'b.formulario_id', '=', 'f.id')
            ->join('organizaciones as o', 'o.id', '=', 'f.organizacion_id')
            ->where('f.organizacion_id', $orgId)
            ->when($periodoId, fn($q) => $q->where('f.periodo_id', $periodoId))
            ->selectRaw('
                f.id,
                p.anio as periodo,
                f.estado,
                COUNT(b.id) as beneficiarios,
                f.created_at,
                o.nota_muni
            ')
            ->groupBy('f.id','p.anio','f.estado','f.created_at','o.nota_muni')
            ->orderByDesc('f.id')
            ->get();
    }



    public function exportOrgFormsSummaryXlsx(int $id, \Illuminate\Http\Request $request)
    {
        $org = \App\Models\Organizacion::findOrFail($id);
        $rows = $this->buildOrgFormsSummary($id, $request->integer('periodo_id'));

        $html = view('municipales.exports.forms-summary-xls', [
            'org'  => $org,
            'rows' => $rows,
        ])->render();

        $filename = 'org_'.$id.'_formularios_resumen_'.now()->format('Ymd_His').'.xls';

        return response($html, 200, [
            'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }



    public function exportOrgFormsSummaryPdf(int $id, \Illuminate\Http\Request $request)
    {
        $org  = \App\Models\Organizacion::findOrFail($id);
        $rows = $this->buildOrgFormsSummary($id, $request->integer('periodo_id'));

        $html = view('municipales.exports.forms-summary-pdf', [
            'org'  => $org,
            'rows' => $rows,
        ])->render();

        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $filename = 'org_'.$id.'_formularios_resumen_'.now()->format('Ymd_His').'.pdf';

        return response($dompdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }


    public function saveNotaMuni(Request $request, int $id)
    {
        $data = $request->validate([
            'nota_muni' => ['nullable','string'],
        ]);

        $org = \App\Models\Organizacion::findOrFail($id);
        $org->nota_muni = $data['nota_muni'] ?? null;
        $org->save();

        return back()->with('status', 'Observación municipal guardada.');
    }


    
}





