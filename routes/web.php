<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizacionController;
use App\Http\Middleware\EnsureOrganizationIsAuthenticated as OrgAuth;

// Login (vista + post)
Route::get('/', [OrganizacionController::class, 'showLoginForm'])->name('organizacion.login.form');
Route::post('/organizacion/login', [OrganizacionController::class, 'login'])->name('organizacion.login.post');

// Panel
Route::get('/inicio', [OrganizacionController::class, 'inicio'])
    ->middleware(OrgAuth::class)
    ->name('panel.inicio');

// Formulario (buscar/crear/editar)
Route::get('/formulario', [OrganizacionController::class, 'showForm'])
    ->middleware(OrgAuth::class)
    ->name('formulario.show');

// Detalle histórico de un formulario
Route::get('/formularios/{id}', [OrganizacionController::class, 'verFormulario'])
    ->middleware(OrgAuth::class)
    ->name('formularios.show');

// Beneficiarios (CRUD)
Route::post('/beneficiario/registrar', [OrganizacionController::class, 'storeBeneficiario'])
    ->middleware(OrgAuth::class)
    ->name('beneficiario.store');

Route::get('/beneficiario/{id}/edit', [OrganizacionController::class, 'edit'])
    ->middleware(OrgAuth::class)
    ->name('beneficiario.edit');

Route::put('/beneficiario/{id}', [OrganizacionController::class, 'update'])
    ->middleware(OrgAuth::class)
    ->name('beneficiario.update');

Route::delete('/beneficiario/{id}', [OrganizacionController::class, 'destroy'])
    ->middleware(OrgAuth::class)
    ->name('beneficiario.destroy');

// Cerrar sesión
Route::post('/organizacion/cerrar', [OrganizacionController::class, 'cerrar'])
    ->middleware(OrgAuth::class)
    ->name('organizacion.cerrar');

// Redirección antigua
Route::get('/login/funcionarios', function () {
    return redirect()->route('panel.inicio');
})->name('login.funcionarios');

Route::get('/formularios/descargar', [OrganizacionController::class, 'descargar'])
    ->middleware(\App\Http\Middleware\EnsureOrganizationIsAuthenticated::class)
    ->name('formularios.descargar');

Route::post('/organizacion/logout', [OrganizacionController::class, 'logout'])
    ->name('organizacion.logout');


Route::get('/formularios/export/xlsx', [OrganizacionController::class, 'exportXlsx'])
    ->middleware(OrgAuth::class)->name('formularios.export.xlsx');

Route::get('/formularios/export/pdf', [OrganizacionController::class, 'exportPdf'])
    ->middleware(OrgAuth::class)->name('formularios.export.pdf');