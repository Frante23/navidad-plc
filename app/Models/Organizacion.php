<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Organizacion extends Model
{
    use HasFactory;

    protected $table = 'organizaciones';
    protected $fillable = [
        'tipo_organizacion_id',
        'nombre',
        'personalidad_juridica',
        'domicilio_despacho',
        'email',
        'nombre_representante',
        'telefono_contacto',
        'observacion',
        'clave',
        'estado'
    ];

    public function tipo()
    {
        return $this->belongsTo(TipoOrganizacion::class, 'tipo_organizacion_id');
    }
}
