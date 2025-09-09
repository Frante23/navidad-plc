<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formulario extends Model
{
    use HasFactory;

    protected $table = 'formularios';
    protected $fillable = [
        'organizacion_id', 'periodo', 'fecha_inicio', 'fecha_cierre', 'estado', 'observacion'
    ];

    public function beneficiarios()
    {
        return $this->hasMany(Beneficiario::class);
    }
}
