<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Support\Rut;

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
        'tramo_id',
        'rut_jefe_hogar',
        'porcentaje_rsh',
        'observaciones',
        'aceptado',
    ];

    protected $casts = [
        'porcentaje_rsh' => 'integer',
    ];



    public function setRutAttribute($value)
    {
        $this->attributes['rut'] = Rut::clean($value);
    }

    public function setRutJefeHogarAttribute($value)
    {
        $this->attributes['rut_jefe_hogar'] = Rut::clean($value);
    }
    public function getRutFormateadoAttribute()
    {
        return Rut::format($this->rut);
    }

    public function getRutJefeHogarFormateadoAttribute()
    {
        return Rut::format($this->rut_jefe_hogar);
    }
    public function formulario()
    {
        return $this->belongsTo(Formulario::class, 'formulario_id');
    }

    public function organizacion()
    {
        return $this->belongsTo(Organizacion::class, 'organizacion_id');
    }

    public function tramo()
    {
        return $this->belongsTo(TramoEdad::class, 'tramo_id'); 
    }

    public function getEdadAttribute()
    {
        return \Carbon\Carbon::parse($this->fecha_nacimiento)->age;
    }
}
