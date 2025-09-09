<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoOrganizacion extends Model
{
    use HasFactory;

    protected $table = 'tipos_organizaciones'; // tabla asociada
    protected $fillable = ['nombre', 'usable'];

    public $timestamps = true; // si quieres usar fecha_creacion, puedes dejarlo como timestamp
}
