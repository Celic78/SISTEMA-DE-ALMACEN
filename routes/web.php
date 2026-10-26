<?php

use Illuminate\Support\Facades\Route;

// Portal Privado (Centro de Mando Operativo)
Route::get('/', function () {
    return view('dashboard');
})->name('dashboard');

// Portal Público (Acceso y Directorio Institucional)
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

// Rutas auxiliares de navegación para vistas operativas
Route::get('/reportes/nuevo', function () {
    return view('dashboard');
})->name('reportes.create');

Route::get('/coordinacion', function () {
    return view('dashboard');
})->name('coordinacion.index');

Route::get('/cuadrillas', function () {
    return view('dashboard');
})->name('cuadrillas.index');

Route::get('/almacen', function () {
    return view('dashboard');
})->name('almacen.index');

Route::post('/logout', function () {
    return redirect()->route('login');
})->name('logout');
