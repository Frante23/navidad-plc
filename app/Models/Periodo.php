<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Periodo extends Model
{
    use HasFactory;

    protected $table = 'periodos'; // nombre de la tabla

    protected $fillable = [
        'anio',
        'fecha_inicio',
        'fecha_cierre',
        'estado',
    ];

    public $timestamps = false; // si no tienes created_at/updated_at
}
