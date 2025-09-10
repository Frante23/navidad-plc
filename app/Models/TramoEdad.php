<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TramoEdad extends Model
{
    use HasFactory;

    protected $table = 'tramos_edad';
    public $timestamps = false;

    protected $fillable = [
        'nombre_tramo',
        'edad_min_meses',
        'edad_max_meses',
        'requiere_sexo',
    ];
}
