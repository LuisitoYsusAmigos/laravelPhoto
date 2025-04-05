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
use App\Http\Controllers\MateriaPrimaVidrioController;
use App\Http\Controllers\StockVarillaController;
use App\Http\Controllers\StockVidrioController;
use App\Http\Controllers\StockTrupanController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CotizadorController;
use App\Http\Controllers\UserController;

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

//materia prima vidrio
Route::get('/materiaPrimaVidrios', [MateriaPrimaVidrioController::class, 'index']);
Route::post('/materiaPrixmaVidrio', [MateriaPrimaVidrioController::class, 'store']);
Route::get('/materiaPrimaVidrio/{id}', [MateriaPrimaVidrioController::class, 'show']);
Route::put('/materiaPrimaVidrio/{id}', [MateriaPrimaVidrioController::class, 'update']);
Route::delete('/materiaPrimaVidrio/{id}', [MateriaPrimaVidrioController::class, 'destroy']);

// stock varila 

Route::get('/stockVarillas', [StockVarillaController::class, 'index']); // Obtener todos los registros
Route::post('/stockVarillas', [StockVarillaController::class, 'store']); // Crear un nuevo registro
Route::get('/stockVarillas/{id}', [StockVarillaController::class, 'show']); // Obtener un registro por ID
Route::put('/stockVarillas/{id}', [StockVarillaController::class, 'update']); // Actualizar un registro
Route::delete('/stockVarillas/{id}', [StockVarillaController::class, 'destroy']); // Eliminar un registro

// stock vidrio
Route::get('/stockVidrios', [StockVidrioController::class, 'index']); // Obtener todos los registros
Route::post('/stockVidrio', [StockVidrioController::class, 'store']); // Crear un nuevo registro
Route::get('/stockVidrio/{id}', [StockVidrioController::class, 'show']); // Obtener un registro por ID
Route::put('/stockVarillas/{id}', [StockVidrioController::class, 'update']); // Actualizar un registro
Route::delete('/stockVarillas/{id}', [StockVidrioController::class, 'destroy']); // Eliminar un registro

//stock trupan
Route::get('/stockTrupans', [StockTrupanController::class, 'index']); // Obtener todos los registros
Route::post('/stockTrupans', [StockTrupanController::class, 'store']); // Crear un nuevo registro
Route::get('/stockTrupans/{id}', [StockTrupanController::class, 'show']); // Obtener un registro por ID
Route::put('/stockTrupans/{id}', [StockTrupanController::class, 'update']); // Actualizar un registro
Route::delete('/stockTrupans/{id}', [StockTrupanController::class, 'destroy']); // Eliminar un registro

//cliente
Route::get('/clientes', [ClienteController::class, 'index']); // Obtener todos los registros
Route::get('/clientes/paginados', [ClienteController::class, 'indexPaginado']); // Obtener registros paginados
Route::get('/clientes/search', [ClienteController::class, 'search']); // Buscar clientes
Route::post('/cliente', [ClienteController::class, 'store']); // Crear un nuevo registro
Route::get('/cliente/{id}', [ClienteController::class, 'show']); // Obtener un registro por ID
Route::put('/cliente/{id}', [ClienteController::class, 'update']); // Actualizar un registro
Route::delete('/cliente/{id}', [ClienteController::class, 'destroy']); // Eliminar un registro


//auth
Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::get('/cotizar',[CotizadorController::class, 'index']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/logout', [AuthController::class, 'logout']);
});

//user
Route::get('/users', [UserController::class, 'index']); // Obtener todos los usuarios
//Route::post('/user', [UserController::class, 'store']); // Crear un nuevo usuario
Route::get('/user/{id}', [UserController::class, 'show']); // Obtener un usuario por ID
Route::put('/user/{id}', [UserController::class, 'update']); // Actualizar un usuario
Route::delete('/user/{id}', [UserController::class, 'destroy']); // Eliminar un usuario
