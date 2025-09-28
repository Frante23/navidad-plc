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


    public function showOrg($id, Request $request)
    {
        $funcionario = auth('func')->user();
        $periodoSel = $request->integer('periodo_id');

        $org = Organizacion::findOrFail($id);

        $formularios = Formulario::withCount('beneficiarios')
            ->when($periodoSel, fn($qq)=>$qq->where('periodo_id', $periodoSel))
            ->where('organizacion_id',$org->id)
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        $periodos = Periodo::orderByDesc('anio')->get();

        return view('municipales.org-show', compact('funcionario','org','formularios','periodos','periodoSel'));
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
        $orgIds = \App\Models\Organizacion::when($q, function ($qq) use ($q) {
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

        $html = view('municipales.exports.table-xls', ['rows' => $rows])->render();
        return response($html, 200, $headers);
    }

    public function exportPdf(Request $request)
        {
            $rows = $this->buildRows($request->integer('periodo_id'), trim($request->get('q','')));

            if (!class_exists(Options::class)) {
                return back()->with('status','PDF no disponible (falta dompdf). Usa Excel.');
            }

            $html = view('municipales.exports.table-pdf', ['rows' => $rows, 'titulo' => 'Exportación agrupada'])->render();

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4','landscape');
            $dompdf->render();

            $filename = 'muni_export_'.now()->format('Ymd_His').'.pdf';
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ]);
        }


        public function exportOrgXlsx($id, Request $request)
        {
            $rows = $this->buildOrgRows((int)$id, $request->integer('periodo_id'));

            $filename = 'org_'.$id.'_export_'.now()->format('Ymd_His').'.xls';
            $headers = [
                'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $html = view('municipales.exports.table-xls', ['rows' => $rows])->render();
            return response($html, 200, $headers);
        }

        public function exportOrgPdf($id, Request $request)
        {
            $rows = $this->buildOrgRows((int)$id, $request->integer('periodo_id'));

            if (!class_exists(Options::class)) {
                return back()->with('status','PDF no disponible (falta dompdf). Usa Excel.');
            }

            $org = \App\Models\Organizacion::findOrFail($id);
            $html = view('municipales.exports.table-pdf', [
                'rows'   => $rows,
                'titulo' => 'Organización: '.$org->nombre.' ('.$org->personalidad_juridica.')'
            ])->render();

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4','landscape');
            $dompdf->render();

            $filename = 'org_'.$id.'_export_'.now()->format('Ymd_His').'.pdf';
            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
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

        public function exportFormXlsx($id)
        {
            $rows = $this->buildFormRows((int)$id);

            $filename = 'formulario_'.$id.'_'.now()->format('Ymd_His').'.xls';
            $headers = [
                'Content-Type'        => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            $html = view('municipales.exports.table-xls', compact('rows'))->render();
            return response($html, 200, $headers);
        }

        public function exportFormPdf($id)
        {
            $rows = $this->buildFormRows((int)$id);

            if (!class_exists(Options::class)) {
                return back()->with('status','PDF no disponible (falta dompdf). Usa Excel.');
            }

            $html = view('municipales.exports.table-pdf', [
                'rows'   => $rows,
                'titulo' => 'Formulario #'.$id
            ])->render();

            $options = new Options();
            $options->set('isRemoteEnabled', true);
            $options->set('defaultFont', 'DejaVu Sans');
            $dompdf = new Dompdf($options);
            $dompdf->loadHtml($html, 'UTF-8');
            $dompdf->setPaper('A4','landscape');
            $dompdf->render();

            $filename = 'formulario_'.$id.'_'.now()->format('Ymd_His').'.pdf';
            return response($dompdf->output(), 200, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
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

        return back()->with('status','Organización aprobada y activada.');
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

        $org->estado = 'activo';
        $org->clave  = Hash::make($data['clave']); 
        $org->save();

        return back()->with('status', 'Organización reactivada con nueva contraseña.');
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

        $org->estado = 'activo';
        $org->clave  = Hash::make($request->clave);
        $org->save();

        return back()->with('status','Organización reactivada correctamente.');
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



    
}





