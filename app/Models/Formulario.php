<?php

namespace App\Models;

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Formulario extends Model
{
    use HasFactory;

    protected $table = 'formularios';

    protected $fillable = [
        'organizacion_id',
        'estado',
        'periodo_id',
    ];

    public function periodo()
    {
        return $this->belongsTo(Periodo::class);
    }

    public function beneficiarios()
    {
        return $this->hasMany(Beneficiario::class);
    }
}

