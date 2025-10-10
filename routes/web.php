<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrganizacionController;
use App\Http\Middleware\EnsureOrganizationIsAuthenticated as OrgAuth;

use App\Http\Controllers\Muni\AuthMuniController;
use App\Http\Controllers\Muni\DashboardMuniController;
use App\Http\Controllers\Muni\FuncionarioMunicipalController;
use App\Http\Controllers\Muni\AuditController;



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

    Route::get(
        '/muni/organizaciones/export/pdf',
        [DashboardMuniController::class, 'exportListadoOrganizacionespdf']
    )->name('muni.orgs.export.pdf');

    Route::get(
        '/muni/estadisticas',
        [DashboardMuniController::class, 'estadisticas']
    )->name('muni.estadisticas');
});




Route::middleware(['auth:func','func.admin'])->get('/debug-admin', function () {
    return 'ok admin';
});


Route::middleware(['auth:func','func.admin'])->group(function () {

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

    Route::get('/muni/organizaciones/crear', [DashboardMuniController::class, 'createOrg'])->name('muni.org.create');
    Route::post('/muni/organizaciones/crear', [DashboardMuniController::class, 'storeOrg'])->name('muni.org.store');

    Route::post('/muni/formulario/{id}/beneficiarios/save', [DashboardMuniController::class, 'bulkSaveBeneficiarios'])
        ->name('muni.form.ben.bulkSave');

    Route::get('/muni/organizacion/{id}/forms/export/xlsx', [DashboardMuniController::class, 'exportOrgFormsSummaryXlsx'])
        ->name('muni.org.forms.export.xlsx');

    Route::get('/muni/organizacion/{id}/forms/export/pdf', [DashboardMuniController::class, 'exportOrgFormsSummaryPdf'])
        ->name('muni.org.forms.export.pdf');

    Route::post('/muni/organizacion/{id}/nota-muni', [DashboardMuniController::class, 'saveNotaMuni'])
        ->name('muni.org.nota.save');



    Route::get('/municipales/funcionarios', [FuncionarioMunicipalController::class, 'index'])
        ->name('funcionarios.index');

    Route::get('/municipales/funcionarios/crear', [FuncionarioMunicipalController::class, 'create'])
        ->name('funcionarios.create');

    Route::post('/municipales/funcionarios', [FuncionarioMunicipalController::class, 'store'])
        ->name('funcionarios.store');

    Route::patch('/municipales/funcionarios/{id}/toggle-admin', [FuncionarioMunicipalController::class, 'toggleAdmin'])
        ->name('funcionarios.toggleAdmin');




    Route::delete('/municipales/funcionarios/{id}', [FuncionarioMunicipalController::class, 'destroy'])
        ->name('funcionarios.destroy');


    Route::get('/muni/auditoria', [AuditController::class, 'index'])->name('muni.auditoria');
});
