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
        'direccion',
        'formulario_id',
        'organizacion_id',
    ];

    public function formulario()
    {
        return $this->belongsTo(Formulario::class, 'formulario_id');
    }

    public function organizacion()
    {
        return $this->belongsTo(Organizacion::class, 'organizacion_id');
    }

    // Edad calculada
    public function getEdadAttribute()
    {
        return \Carbon\Carbon::parse($this->fecha_nacimiento)->age;
    }
}
