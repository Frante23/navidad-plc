<?php

namespace App\Http\Controllers\Muni;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuditController extends Controller
{
    public function index(Request $r)
    {
        $q       = trim($r->input('q',''));
        $actorId = $r->input('actor_id');
        $accion  = $r->input('accion');
        $desde   = $r->input('desde');
        $hasta   = $r->input('hasta');

        $sql = DB::table('vw_auditoria')->orderByDesc('created_at');

        if ($q !== '') {
            $like = "%$q%";
            $sql->where(function($w) use ($like) {
                $w->where('actor_correo','like',$like)
                  ->orWhere('actor_nombre','like',$like)
                  ->orWhere('accion','like',$like)
                  ->orWhere('descripcion','like',$like);
            });
        }
        if ($actorId) $sql->where('actor_id', $actorId);
        if ($accion)  $sql->where('accion', $accion);
        if ($desde)   $sql->whereDate('created_at','>=',$desde);
        if ($hasta)   $sql->whereDate('created_at','<=',$hasta);

        $logs = DB::table('vw_auditoria')
            ->orderByDesc('created_at')
            ->paginate(30)
            ->appends($r->query());


        $funcionarios = DB::table('funcionarios_municipales')
            ->select('id','nombre_completo','correo')
            ->orderBy('nombre_completo')->get();

        $acciones = [
            'AUTH_LOGIN','AUTH_LOGOUT',
            'FUNC_CREATE','FUNC_GRANT_ADMIN','FUNC_REVOKE_ADMIN','FUNC_DELETE',
            'ORG_CREATE','ORG_CREATE_VIEW','ORG_PENDING_VIEW','ORG_APPROVE','ORG_REJECT',
            'ORG_DEACTIVATE','ORG_REACTIVATE','ORG_ACTIVATE_FROM_INACTIVE','ORG_STATUS_SET','ORG_NOTE_SAVE',
            'DUP_VIEW','DASHBOARD_VIEW','ORG_VIEW','STATS_VIEW',
            'EXPORT_CSV','EXPORT_XLSX','EXPORT_PDF','EXPORT_ORG_XLSX','EXPORT_ORG_PDF','EXPORT_FORM_XLSX','EXPORT_FORM_PDF',
            'FORM_BEN_VIEW','BEN_REVIEW','BEN_BULK_SAVE',
        ];


        return view('municipales.auditoria', compact('logs','funcionarios','acciones','q','actorId','accion','desde','hasta'));
    }
}
