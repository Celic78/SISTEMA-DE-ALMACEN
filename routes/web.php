<?php

use App\Controllers\AlmacenController;
use App\Controllers\AuthController;
use App\Controllers\CoordinacionController;
use App\Controllers\PublicController;
use App\Controllers\ReporteController;
use App\Controllers\TecnicoController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Público / Institucional
|--------------------------------------------------------------------------
*/
Route::get('/', [PublicController::class, 'index'])->name('home');
Route::get('/contacto', [PublicController::class, 'contacto'])->name('contacto');

/*
|--------------------------------------------------------------------------
| Autenticación Institucional (SIMIC RBAC)
|--------------------------------------------------------------------------
*/
Route::get('/login', [AuthController::class, 'login'])->name('login');
Route::post('/login', [AuthController::class, 'autenticar'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Centro de Mando / Dashboard General
|--------------------------------------------------------------------------
*/
Route::get('/dashboard', function () {
    if (Auth::check()) {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $authService = app(\App\Services\AuthService::class);
        $destino = $authService->determinarRutaDestino($user, session('rol_activo'));

        if ($destino !== '/dashboard' && $destino !== route('dashboard', [], false)) {
            return redirect($destino);
        }
    }

    return view('dashboard');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Módulo de Reportes de Incidencias (Portal Solicitante)
|--------------------------------------------------------------------------
*/
Route::prefix('reportes')->name('reportes.')->group(function () {
    Route::get('/nuevo', [ReporteController::class, 'crear'])->name('create');
    Route::get('/crear', [ReporteController::class, 'crear'])->name('crear');
    Route::post('/', [ReporteController::class, 'guardar'])->name('store');
    Route::get('/', [ReporteController::class, 'index'])->name('index');
    Route::get('/mis-reportes', [ReporteController::class, 'index'])->name('mis-reportes');
});

/*
|--------------------------------------------------------------------------
| Panel de Coordinación Operativa & Despacho
|--------------------------------------------------------------------------
*/
Route::prefix('coordinacion')->name('coordinacion.')->group(function () {
    Route::get('/', [CoordinacionController::class, 'dashboard'])->name('index');
    Route::get('/duplicados', [CoordinacionController::class, 'matrizDuplicados'])->name('duplicados');
    Route::post('/consolidar-duplicados', [CoordinacionController::class, 'consolidarFolioMaestro'])->name('consolidar');
    Route::post('/asignar-cuadrilla', [CoordinacionController::class, 'asignarCuadrilla'])->name('asignar');
});

/*
|--------------------------------------------------------------------------
| Consola Técnica (Cuadrillas)
|--------------------------------------------------------------------------
*/
Route::get('/cuadrillas', [TecnicoController::class, 'misOrdenes'])->name('cuadrillas.index');
Route::prefix('tecnico')->name('tecnico.')->group(function () {
    Route::get('/mis-ordenes', [TecnicoController::class, 'misOrdenes'])->name('ordenes');
    Route::post('/actualizar-orden', [TecnicoController::class, 'actualizarEstado'])->name('actualizar');
    Route::post('/solicitar-material', [TecnicoController::class, 'solicitarMaterial'])->name('solicitar-material');
    Route::post('/estimar-tiempo', [TecnicoController::class, 'estimarTiempo'])->name('estimar-tiempo');
});

/*
|--------------------------------------------------------------------------
| Inventario & Suministros (Almacén)
|--------------------------------------------------------------------------
*/
Route::get('/almacen', [AlmacenController::class, 'inventario'])->name('almacen.index');
Route::prefix('almacen')->name('almacen.')->group(function () {
    Route::get('/inventario', [AlmacenController::class, 'inventario'])->name('inventario');
    Route::get('/requisiciones', [AlmacenController::class, 'requisicionesPendientes'])->name('requisiciones');
    Route::post('/despachar-vale', [AlmacenController::class, 'despacharMaterial'])->name('despachar');
    Route::post('/entrada', [AlmacenController::class, 'despacharMaterial'])->name('entrada');
});
