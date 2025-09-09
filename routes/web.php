<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizacionController;

// Página de login
Route::get('/', function () {
    return view('organizaciones.login-organizacion'); // ojo: nombre correcto del blade
})->name('login.organizacion');

// Login POST
Route::post('/login/organizacion', [OrganizacionController::class, 'login'])
    ->name('organizacion.login.post');

// Formulario beneficiarios
Route::get('/formulario', [OrganizacionController::class, 'showForm'])
    ->name('formulario');

// Guardar beneficiario
Route::post('/beneficiario/registrar', [OrganizacionController::class, 'storeBeneficiario'])
    ->name('beneficiario.store');

// Cualquier intento de login de funcionarios redirige al formulario de organizaciones
Route::get('/login/funcionarios', function () {
    return redirect()->route('formulario');
})->name('login.funcionarios');
