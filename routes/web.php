<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizacionController;


Route::get('/', function () {
    return view('organizaciones.login-organizacion');
})->name('login.organizacion');

Route::post('/login/organizacion', function () {
    return redirect()->route('formulario'); 
})->name('organizacion.login.post');


Route::get('/formulario', [OrganizacionController::class, 'showForm'])->name('formulario');
Route::post('/beneficiario/registrar', [OrganizacionController::class, 'storeBeneficiario'])->name('beneficiario.store');


Route::get('/login/funcionarios', function () {
    return view('organizaciones.formulario');
})->name('login.funcionarios');

