<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizacionController;
use App\Http\Middleware\EnsureOrganizationIsAuthenticated as OrgAuth;
use App\Http\Controllers\Muni\AuthMuniController;
use App\Http\Controllers\Muni\DashboardMuniController;



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


Route::get('/formularios/descargar', [OrganizacionController::class, 'descargar'])
    ->middleware(\App\Http\Middleware\EnsureOrganizationIsAuthenticated::class)
    ->name('formularios.descargar');

Route::post('/organizacion/logout', [OrganizacionController::class, 'logout'])
    ->name('organizacion.logout');


Route::get('/formularios/export/xlsx', [OrganizacionController::class, 'exportXlsx'])
    ->middleware(OrgAuth::class)->name('formularios.export.xlsx');

Route::get('/formularios/export/pdf', [OrganizacionController::class, 'exportPdf'])
    ->middleware(OrgAuth::class)->name('formularios.export.pdf');







Route::get('/login/funcionarios', [AuthMuniController::class, 'showLoginForm'])
    ->name('login.funcionarios');

Route::post('/login/funcionarios', [AuthMuniController::class, 'login'])
    ->name('login.funcionarios.post');

Route::post('/logout/funcionarios', [AuthMuniController::class, 'logout'])
    ->name('logout.funcionarios');

Route::middleware('auth:func')->group(function () {
    Route::get('/muni', [DashboardMuniController::class, 'index'])
        ->name('muni.dashboard');
});


Route::middleware('auth:func')->group(function () {
    Route::get('/muni', [DashboardMuniController::class, 'index'])->name('muni.dashboard');
    Route::get('/muni/organizacion/{id}', [DashboardMuniController::class, 'showOrg'])->name('muni.org.show');

    Route::get('/muni/export/xlsx', [DashboardMuniController::class, 'exportXlsx'])->name('muni.export.xlsx');
    Route::get('/muni/export/pdf',  [DashboardMuniController::class, 'exportPdf'])->name('muni.export.pdf');

    Route::get('/muni/organizacion/{id}/export/xlsx', [DashboardMuniController::class, 'exportOrgXlsx'])->name('muni.org.export.xlsx');
    Route::get('/muni/organizacion/{id}/export/pdf',  [DashboardMuniController::class, 'exportOrgPdf'])->name('muni.org.export.pdf');

    Route::get('/muni/formulario/{id}/export/xlsx', [DashboardMuniController::class, 'exportFormXlsx'])
        ->name('muni.form.export.xlsx');

    Route::get('/muni/formulario/{id}/export/pdf',  [DashboardMuniController::class, 'exportFormPdf'])
        ->name('muni.form.export.pdf');


    Route::get('/muni/organizaciones/crear', [DashboardMuniController::class, 'createOrg'])->name('muni.org.create');
    Route::post('/muni/organizaciones/crear', [DashboardMuniController::class, 'storeOrg'])->name('muni.org.store');



    Route::get('/muni/duplicados', [DashboardMuniController::class, 'duplicados'])->name('muni.duplicados');


});

Route::get('/login', fn () => redirect()->route('login.funcionarios'))->name('login');