<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubCategoriaController;
use App\Http\Controllers\MateriaPrimaVarillaController;
use App\Http\Controllers\MateriaPrimaTrupanController;

use App\Http\Controllers\CotizadorController;

// Sucursales

Route::get('/sucursales', [SucursalController::class, 'index']);
Route::get('/sucursal/{id}', [SucursalController::class, 'show']);
Route::post('/sucursal', [SucursalController::class, 'store']);
Route::put('/sucursal/{id}', [SucursalController::class, 'update']);
Route::delete('/sucursal/{id}', [SucursalController::class, 'destroy']);
  

//categorias
Route::get('/categorias', [CategoriaController::class, 'index']);
Route::post('/categoria', [CategoriaController::class, 'store']);
Route::get('/categoria/{id}', [CategoriaController::class, 'show']);
Route::put('/categoria/{id}', [CategoriaController::class, 'update']);
Route::delete('/categoria/{id}', [CategoriaController::class, 'destroy']);

// subcategoria
Route::get('/subCategorias', [SubCategoriaController::class, 'index']);
Route::post('/subCategoria', [SubCategoriaController::class, 'store']);
Route::get('/subCategoria/{id}', [SubCategoriaController::class, 'show']);
Route::put('/subCategoria/{id}', [SubCategoriaController::class, 'update']);
Route::delete('/subCategoria/{id}', [SubCategoriaController::class, 'destroy']);
//producto

Route::get('/productos', [ProductoController::class, 'index']);
Route::post('/producto', [ProductoController::class, 'store']);
Route::get('/producto/{id}', [ProductoController::class, 'show']);
Route::put('/producto/{id}', [ProductoController::class, 'update']);
Route::delete('/producto/{id}', [ProductoController::class, 'destroy']);

// mateira prima varilla
// Materia Prima Varillas
Route::get('/materiaPrimaVarillas', [MateriaPrimaVarillaController::class, 'index']);
Route::post('/materiaPrimaVarilla', [MateriaPrimaVarillaController::class, 'store']);
Route::get('/materiaPrimaVarilla/{id}', [MateriaPrimaVarillaController::class, 'show']);
Route::put('/materiaPrimaVarilla/{id}', [MateriaPrimaVarillaController::class, 'update']);
Route::delete('/materiaPrimaVarilla/{id}', [MateriaPrimaVarillaController::class, 'destroy']);

// mateira prima trupan
Route::get('/materiaPrimaTrupanes', [MateriaPrimaTrupanController::class, 'index']);
Route::post('/materiaPrimaTrupan', [MateriaPrimaTrupanController::class, 'store']);
Route::get('/materiaPrimaTrupan/{id}', [MateriaPrimaTrupanController::class, 'show']);
Route::put('/materiaPrimaTrupan/{id}', [MateriaPrimaTrupanController::class, 'update']);
Route::delete('/materiaPrimaTrupan/{id}', [MateriaPrimaTrupanController::class, 'destroy']);




//auth
Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::get('/cotizar',[CotizadorController::class, 'index']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/logout', [AuthController::class, 'logout']);
});
