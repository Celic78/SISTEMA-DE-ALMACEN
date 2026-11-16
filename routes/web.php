<?php

use App\Controllers\AuthController;
use App\Http\Controllers\PortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PortalController::class, 'home'])->name('home');
Route::get('/ayuda', [PortalController::class, 'help'])->name('help');
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'autenticar'])->middleware('throttle:6,1')->name('login.store');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::middleware('auth')->group(function (): void {
    Route::get('/panel', [PortalController::class, 'dashboard'])->name('dashboard');
    Route::get('/reportes/nuevo', [PortalController::class, 'createReport'])->name('reportes.create');
    Route::get('/reportes', [PortalController::class, 'reports'])->name('reportes.index');
    Route::post('/reportes', [PortalController::class, 'storeReport'])->name('reportes.store');
    Route::get('/reportes/{solicitud}', [PortalController::class, 'report'])->name('reportes.show');
    Route::middleware('role:Coordinador,Coordinación')->prefix('coordinacion')->name('coordinacion.')->group(function (): void {
        Route::get('/', [PortalController::class, 'coordination'])->name('index');
        Route::get('/incidencias', [PortalController::class, 'incidents'])->name('incidencias');
        Route::get('/duplicados', [PortalController::class, 'duplicates'])->name('duplicados');
        Route::post('/duplicados', [PortalController::class, 'groupReports'])->name('agrupar');
        Route::get('/cuadrillas', [PortalController::class, 'assignment'])->name('cuadrillas');
        Route::post('/asignar', [PortalController::class, 'assign'])->name('asignar');
        Route::get('/autorizaciones', [PortalController::class, 'approvals'])->name('autorizaciones');
        Route::post('/autorizaciones/{orden}', [PortalController::class, 'decide'])->name('decidir');
    });
    Route::middleware('role:Tecnico,Técnico,Cuadrilla')->group(function (): void {
        Route::get('/cuadrillas', [PortalController::class, 'orders'])->name('cuadrillas.index');
        Route::get('/cuadrillas/{orden}', [PortalController::class, 'order'])->name('cuadrillas.show');
        Route::post('/cuadrillas/{orden}/avance', [PortalController::class, 'progress'])->name('cuadrillas.avance');
        Route::post('/cuadrillas/{orden}/tareas/{tarea}', [PortalController::class, 'task'])->name('cuadrillas.tarea');
        Route::get('/cuadrillas/{orden}/materiales', [PortalController::class, 'requestMaterials'])->name('cuadrillas.materiales');
        Route::post('/cuadrillas/{orden}/materiales', [PortalController::class, 'storeMaterials'])->name('cuadrillas.materiales.store');
        Route::get('/cuadrillas/{orden}/cierre', [PortalController::class, 'closure'])->name('cuadrillas.cierre');
        Route::post('/cuadrillas/{orden}/cierre', [PortalController::class, 'close'])->name('cuadrillas.cerrar');
    });
    Route::middleware('role:Almacen,Almacén,Almacenista')->prefix('almacen')->name('almacen.')->group(function (): void {
        Route::get('/', [PortalController::class, 'inventory'])->name('index');
        Route::get('/solicitudes', [PortalController::class, 'requisitions'])->name('solicitudes');
        Route::redirect('/requisiciones', '/almacen/solicitudes');
        Route::get('/solicitudes/{requisicion}', [PortalController::class, 'delivery'])->name('entrega');
        Route::post('/solicitudes/{requisicion}', [PortalController::class, 'dispatch'])->name('despachar');
        Route::get('/entradas', [PortalController::class, 'entry'])->name('entrada');
        Route::post('/entradas', [PortalController::class, 'storeEntry'])->name('entrada.store');
        Route::get('/bitacora', [PortalController::class, 'ledger'])->name('bitacora');
        Route::get('/bitacora/exportar', [PortalController::class, 'exportLedger'])->name('exportar');
    });
});
