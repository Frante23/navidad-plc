<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Beneficiario extends Model
{
    use HasFactory;

    protected $table = 'beneficiarios';

    protected $fillable = [
        'rut',
        'nombre_completo',
        'fecha_nacimiento',
        'sexo',
        'formulario_id'
    ];

    // Laravel por defecto usa created_at y updated_at, pero tu tabla usa fecha_creacion
    const CREATED_AT = 'fecha_creacion';
    const UPDATED_AT = null; // si no tienes updated_at


    public function formulario()
    {
        return $this->belongsTo(Formulario::class);
    }
}
