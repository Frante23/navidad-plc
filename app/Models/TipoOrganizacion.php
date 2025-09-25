<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoOrganizacion extends Model
{
    use HasFactory;

    protected $table = 'tipos_organizaciones'; 
    protected $fillable = ['nombre', 'usable', 'fecha_creacion'];

    public $timestamps = false; 
}
