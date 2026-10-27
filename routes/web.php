<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Público / Acceso Institucional
|--------------------------------------------------------------------------
*/
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/logout', function () {
    return redirect()->route('login');
})->name('logout');

/*
|--------------------------------------------------------------------------
| Portal Privado (Centro de Mando Operativo)
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

/*
|--------------------------------------------------------------------------
| Módulos Operativos
| 
| Los siguientes middlewares están registrados en bootstrap/app.php y listos
| para aplicarse según el nivel de protección requerido:
| - 'role:NombreRol'                  -> Valida roles (ej. Coordinador, Almacen, Tecnico)
| - 'email.tipo:alumno,profesor'      -> Valida la categoría de cuenta institucional
| - 'tecnico.asignado'               -> Restringe gestión de orden al técnico responsable
| - 'solicitud.propietario'          -> Restringe ver/editar orden al solicitante original
| - 'orden.aprobada.material'        -> Bloquea despacho de almacén si requiere visto bueno
| - 'bitacora'                       -> Registra mutaciones (POST, PUT, DELETE) en BD
| - 'force.json'                     -> Garantiza respuestas JSON en endpoints API
| - 'security.headers'               -> Encabezados HTTP de seguridad (activo globalmente en web)
|--------------------------------------------------------------------------
*/

// Reporte de Incidencias (Portal Solicitante)
Route::get('/reportes/nuevo', function () {
    return view('dashboard');
})->name('reportes.create');

// Panel de Coordinación (Supervisión y Aprobaciones)
Route::get('/coordinacion', function () {
    return view('dashboard');
})->name('coordinacion.index');

// Consola Técnica (Cuadrillas)
Route::get('/cuadrillas', function () {
    return view('dashboard');
})->name('cuadrillas.index');

// Inventario & Bitácora de Movimientos (Almacén)
Route::get('/almacen', function () {
    return view('dashboard');
})->name('almacen.index');

