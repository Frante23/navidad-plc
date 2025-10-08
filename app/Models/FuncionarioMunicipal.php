<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class FuncionarioMunicipal extends Authenticatable
{
    protected $table = 'funcionarios_municipales';

    protected $fillable = [
        'nombre_completo','rut','correo','telefono_contacto','cargo','password','remember_token','last_login_at', 'es_admin'
    ];

    protected $hidden = ['password','remember_token'];

    protected $casts = [
        'es_admin' => 'boolean',
    ];


    public function getAuthIdentifierName()
    {
        return 'correo';
    }
}
