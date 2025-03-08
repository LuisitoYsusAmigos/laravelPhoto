<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\sucursalController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SubCategoriaController;

use App\Http\Controllers\CotizadorController;


//sucursales
Route::get('/sucursales',[sucursalController::class, 'index']);
Route::get('/sucursal/{id}', [sucursalController::class, 'show']);
Route::post('/sucursal',[sucursalController::class, 'store']);
Route::put('/sucursal/{id}',[sucursalController::class, 'vj,m ']);
Route::patch('/sucursal/{id}',[sucursalController::class, 'updatePartial']);
Route::delete('/sucursal/{id}', [sucursalController::class, 'destroy'] );
//categorias

Route::get('/categorias', [CategoriaController::class, 'index']);
Route::post('/categoria', [CategoriaController::class, 'store']);
Route::get('/categoria/{id}', [CategoriaController::class, 'show']);
Route::put('/categoria/{id}', [CategoriaController::class, 'update']);
Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy']);

// subcategoria
Route::get('/subCategorias', [SubCategoriaController::class, 'index']);
Route::post('/subCategoria', [SubCategoriaController::class, 'store']);
Route::get('/subCategoria/{id}', [SubCategoriaController::class, 'show']);
Route::put('/subCategoria/{id}', [SubCategoriaController::class, 'update']);
Route::delete('/subCategorias/{id}', [SubCategoriaController::class, 'destroy']);
//producto

Route::get('/productos', [ProductoController::class, 'index']);
Route::post('/producto', [ProductoController::class, 'store']);
Route::get('/producto/{id}', [ProductoController::class, 'show']);
Route::put('/producto/{id}', [ProductoController::class, 'update']);
Route::delete('/producto/{id}', [ProductoController::class, 'destroy']);




//auth
Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::get('/cotizar',[CotizadorController::class, 'index']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/logout', [AuthController::class, 'logout']);
});
