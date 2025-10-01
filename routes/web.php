<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizacionController;
use App\Http\Middleware\EnsureOrganizationIsAuthenticated as OrgAuth;
use App\Http\Controllers\Muni\AuthMuniController;
use App\Http\Controllers\Muni\DashboardMuniController;

Route::get('/', [OrganizacionController::class, 'showLoginForm'])->name('organizacion.login.form');
Route::post('/organizacion/login', [OrganizacionController::class, 'login'])->name('organizacion.login.post');

Route::get('/registro', [OrganizacionController::class, 'showRegisterForm'])->name('organizacion.register.form');
Route::post('/registro', [OrganizacionController::class, 'register'])->name('organizacion.register.post');

Route::middleware(OrgAuth::class)->group(function () {
    Route::get('/inicio', [OrganizacionController::class, 'inicio'])->name('panel.inicio');

    Route::get('/formulario', [OrganizacionController::class, 'showForm'])->name('formulario.show');
    Route::get('/formularios/{id}', [OrganizacionController::class, 'verFormulario'])->name('formularios.show');

    Route::post('/beneficiario/registrar', [OrganizacionController::class, 'storeBeneficiario'])->name('beneficiario.store');
    Route::get('/beneficiario/{id}/edit', [OrganizacionController::class, 'edit'])->name('beneficiario.edit');
    Route::put('/beneficiario/{id}', [OrganizacionController::class, 'update'])->name('beneficiario.update');
    Route::delete('/beneficiario/{id}', [OrganizacionController::class, 'destroy'])->name('beneficiario.destroy');

    Route::post('/organizacion/cerrar', [OrganizacionController::class, 'cerrar'])->name('organizacion.cerrar');

    Route::get('/formularios/descargar', [OrganizacionController::class, 'descargar'])->name('formularios.descargar');
    Route::get('/formularios/export/xlsx', [OrganizacionController::class, 'exportXlsx'])->name('formularios.export.xlsx');
    Route::get('/formularios/export/pdf',  [OrganizacionController::class, 'exportPdf'])->name('formularios.export.pdf');

    Route::post('/organizacion/logout', [OrganizacionController::class, 'logout'])->name('organizacion.logout');
});

Route::get('/login/funcionarios', [AuthMuniController::class, 'showLoginForm'])->name('login.funcionarios');
Route::post('/login/funcionarios', [AuthMuniController::class, 'login'])->name('login.funcionarios.post');
Route::post('/logout/funcionarios', [AuthMuniController::class, 'logout'])->name('logout.funcionarios');
Route::get('/login', fn () => redirect()->route('login.funcionarios'))->name('login');

Route::middleware('auth:func')->group(function () {

    Route::get('/muni', [DashboardMuniController::class, 'index'])->name('muni.dashboard');

    Route::get('/muni/organizacion/{id}', [DashboardMuniController::class, 'showOrg'])->name('muni.org.show');
    Route::post('/muni/organizacion/{id}/status', [DashboardMuniController::class, 'setOrgStatus'])->name('muni.org.setStatus');
    Route::post('/muni/organizacion/{id}/desactivar', [DashboardMuniController::class, 'orgDesactivar'])->name('muni.org.desactivar');
    Route::post('/muni/organizacion/{id}/reactivar', [DashboardMuniController::class, 'orgReactivar'])->name('muni.org.reactivar');

    Route::get('/muni/pendientes', [DashboardMuniController::class, 'orgPendientes'])->name('muni.org.pendientes');
    Route::post('/muni/pendientes/{id}/aprobar', [DashboardMuniController::class, 'orgAprobar'])->name('muni.org.aprobar');
    Route::post('/muni/pendientes/{id}/rechazar', [DashboardMuniController::class, 'orgRechazar'])->name('muni.org.rechazar');

    Route::post('/muni/inactivas/{id}/activar', [DashboardMuniController::class, 'orgActivarInactiva'])->name('muni.org.activarInactiva');

    Route::get('/muni/export/xlsx', [DashboardMuniController::class, 'exportXlsx'])->name('muni.export.xlsx');
    Route::get('/muni/export/pdf',  [DashboardMuniController::class, 'exportPdf'])->name('muni.export.pdf');
    Route::get('/muni/organizacion/{id}/export/xlsx', [DashboardMuniController::class, 'exportOrgXlsx'])->name('muni.org.export.xlsx');
    Route::get('/muni/organizacion/{id}/export/pdf',  [DashboardMuniController::class, 'exportOrgPdf'])->name('muni.org.export.pdf');
    Route::get('/muni/formulario/{id}/export/xlsx', [DashboardMuniController::class, 'exportFormXlsx'])->name('muni.form.export.xlsx');
    Route::get('/muni/formulario/{id}/export/pdf',  [DashboardMuniController::class, 'exportFormPdf'])->name('muni.form.export.pdf');

    Route::get('/muni/duplicados', [DashboardMuniController::class, 'duplicados'])->name('muni.duplicados');

    Route::post('/muni/beneficiarios/{id}/review', [DashboardMuniController::class, 'reviewBeneficiario'])->name('muni.ben.review');
    Route::get('/muni/formulario/{id}/beneficiarios', [DashboardMuniController::class, 'formBeneficiarios'])->name('muni.form.beneficiarios');

    Route::get('/muni/organizaciones/crear', [\App\Http\Controllers\Muni\DashboardMuniController::class, 'createOrg'])
        ->name('muni.org.create');
    Route::post('/muni/organizaciones/crear', [\App\Http\Controllers\Muni\DashboardMuniController::class, 'storeOrg'])
        ->name('muni.org.store');




    Route::get('/muni/organizaciones/export/pdf', [DashboardMuniController::class, 'exportListadoOrganizacionespdf'])
    ->name('muni.orgs.export.pdf');
    Route::get('/muni/estadisticas', [DashboardMuniController::class, 'estadisticas'])
        ->name('muni.estadisticas');

    Route::get('/muni/estadisticas/org/{id}', function($id, \Illuminate\Http\Request $r) {
        return redirect()->route('muni.estadisticas', ['org_id'=>$id] + $r->only('periodo_id'));
    })->name('muni.estadisticas.org');



});
