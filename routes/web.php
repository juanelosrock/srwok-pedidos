<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Pantalla de inicio: selección de ciudad y dirección
Route::get('/', [HomeController::class, 'index'])->name('home');

// API — Ciudad y dirección
Route::post('/api/ciudades',          [HomeController::class, 'ciudades'])->name('api.ciudades');
Route::post('/api/validar-direccion', [HomeController::class, 'validarDireccion'])->name('api.validar-direccion');

// Menú
Route::get('/menu', [MenuController::class, 'index'])->name('menu');

// API — Menú y productos
Route::post('/api/menu',      [MenuController::class, 'menu'])->name('api.menu');
Route::post('/api/producto',  [MenuController::class, 'producto'])->name('api.producto');
Route::post('/api/combos',    [MenuController::class, 'combos'])->name('api.combos');
Route::post('/api/adiciones', [MenuController::class, 'adiciones'])->name('api.adiciones');

// API — Pedidos
Route::post('/api/pedido',        [OrderController::class, 'enviar'])->name('api.pedido');
Route::post('/api/estado-pedido', [OrderController::class, 'estado'])->name('api.estado-pedido');
