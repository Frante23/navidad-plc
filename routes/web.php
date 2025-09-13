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


Route::post('/organizacion/cerrar', [OrganizacionController::class, 'cerrar'])
    ->name('organizacion.cerrar');



Route::get('/organizacion/login', [OrganizacionController::class, 'showLoginForm'])
    ->name('organizacion.login.form');

Route::post('/organizacion/login', [OrganizacionController::class, 'login'])
    ->name('organizacion.login.post');


Route::get('/beneficiario/{id}/edit', [OrganizacionController::class, 'edit'])->name('beneficiario.edit');
Route::put('/beneficiario/{id}', [OrganizacionController::class, 'update'])->name('beneficiario.update');
Route::delete('/beneficiario/{id}', [OrganizacionController::class, 'destroy'])->name('beneficiario.destroy');
