<?php

namespace App\Support;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class Audit
{
    public static function log(
        $actorId,
        string $accion,
        ?string $entidad = null,
        $entidadId = null,
        ?string $descripcion = null,
        array $extra = []
    ): void {
        $current = Auth::guard('func')->user();

        if ($actorId === '' || $actorId === false) $actorId = null;
        if ($actorId === null && $current) $actorId = $current->id;
        $actorId   = $actorId === null ? null : (int)$actorId;
        $entidadId = $entidadId === null ? null : (int)$entidadId;

        $correo = null;
        $nombre = null;

        if (!is_null($actorId)) {
            try {
                $row = DB::table('funcionarios_municipales')
                        ->where('id', $actorId)
                        ->select('correo','nombre_completo')
                        ->first();
                if ($row) {
                    $correo = $row->correo ?? null;
                    $nombre = $row->nombre_completo ?? null;
                }
            } catch (\Throwable $e) { /* noop */ }
        }
        if (!$correo && $current) $correo = $current->correo ?? null;
        if (!$nombre && $current) $nombre = $current->nombre_completo ?? null;

        DB::table('auditoria')->insert([
            'actor_id'     => $actorId,
            'actor_nombre' => $nombre,   // <- guardamos nombre
            'actor_correo' => $correo,
            'accion'       => $accion,
            'entidad'      => $entidad,
            'entidad_id'   => $entidadId,
            'descripcion'  => $descripcion,
            'ip'           => request()->ip(),
            'user_agent'   => substr((string) request()->header('User-Agent'), 0, 255),
            'extra_json'   => json_encode($extra, JSON_UNESCAPED_UNICODE),
            'created_at'   => now(),
        ]);
    }
}
