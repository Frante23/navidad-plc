<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class FuncionarioMunicipal extends Authenticatable
{
    protected $table = 'funcionarios_municipales';

    protected $fillable = [
        'nombre_completo','rut','correo','telefono_contacto','cargo','password','remember_token','last_login_at'
    ];

    protected $hidden = ['password','remember_token'];

    public function getAuthIdentifierName()
    {
        return 'nombre_completo';
    }
}
