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
use App\Http\Controllers\MateriaPrimaContornoController;

use App\Http\Controllers\StockVarillaController;
use App\Http\Controllers\StockVidrioController;
use App\Http\Controllers\StockTrupanController;
use App\Http\Controllers\StockProductoController;
use App\Http\Controllers\StockContornoController;

use App\Http\Controllers\VentaController;

use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CotizadorController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FuncionesGeneralesController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\LugarController;

//Lugares
Route::get('/lugares', [LugarController::class, 'index']);
Route::post('/lugar', [LugarController::class, 'store']);
Route::get('/lugar/{id}', [LugarController::class, 'show']);
Route::put('/lugar/{id}', [LugarController::class, 'update']);
Route::delete('/lugar/{id}', [LugarController::class, 'destroy']);  

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
Route::get('/categorias/tipo/{palabraX}', [CategoriaController::class, 'indexPorTipo']);

// subcategoria
Route::get('/subCategorias', [SubCategoriaController::class, 'index']);
Route::get('/subCategorias/categoria/{id_categoria}', [SubCategoriaController::class, 'porCategoria']);
Route::post('/subCategoria', [SubCategoriaController::class, 'store']);
Route::get('/subCategoria/{id}', [SubCategoriaController::class, 'show']);
Route::put('/subCategoria/{id}', [SubCategoriaController::class, 'update']);
Route::delete('/subCategoria/{id}', [SubCategoriaController::class, 'destroy']);
Route::get('/subCategoria/porCategoria/{id}', [SubCategoriaController::class, 'subcategoriasPorCategoria']);

//producto

Route::get('/productos', [ProductoController::class, 'index']);
Route::post('/producto', [ProductoController::class, 'store']);
Route::get('/producto/{id}', [ProductoController::class, 'show']);
Route::post('/producto/edit/{id}', [ProductoController::class, 'update']);
Route::delete('/producto/{id}', [ProductoController::class, 'destroy']);
Route::get('/productos/search', [ProductoController::class, 'search']);
Route::get('/productos/search-categorias', [ProductoController::class, 'searchCategorias']);
Route::get('/productos/paginados', [ProductoController::class, 'indexPaginado']);


// mateira prima varilla
// Materia Prima Varillas
Route::get('/materiaPrimaVarillas', [MateriaPrimaVarillaController::class, 'index']);
Route::post('/materiaPrimaVarilla', [MateriaPrimaVarillaController::class, 'store']);
Route::get('/materiaPrimaVarilla/{id}', [MateriaPrimaVarillaController::class, 'show']);
Route::post('/materiaPrimaVarilla/edit/{id}', [MateriaPrimaVarillaController::class, 'update']);
Route::delete('/materiaPrimaVarilla/{id}', [MateriaPrimaVarillaController::class, 'destroy']);

// Funciones adicionales para Materia Prima Varilla
Route::get('/materiaPrimaVarillas/paginados', [MateriaPrimaVarillaController::class, 'indexPaginado']);
Route::get('/materiaPrimaVarillas/search', [MateriaPrimaVarillaController::class, 'search']);
Route::get('/materiaPrimaVarillas/search-categorias', [MateriaPrimaVarillaController::class, 'searchCategorias']);


// mateira prima trupan
Route::get('/materiaPrimaTrupanes', [MateriaPrimaTrupanController::class, 'index']);
Route::post('/materiaPrimaTrupan', [MateriaPrimaTrupanController::class, 'store']);
Route::get('/materiaPrimaTrupan/{id}', [MateriaPrimaTrupanController::class, 'show']);
Route::post('/materiaPrimaTrupan/edit/{id}', [MateriaPrimaTrupanController::class, 'update']);
Route::delete('/materiaPrimaTrupan/{id}', [MateriaPrimaTrupanController::class, 'destroy']);

Route::get('/materiaPrimaTrupanes/paginados', [MateriaPrimaTrupanController::class, 'indexPaginado']); // Listado paginado
Route::get('/materiaPrimaTrupanes/search', [MateriaPrimaTrupanController::class, 'search']); // Búsqueda general
Route::get('/materiaPrimaTrupanes/search-categorias', [MateriaPrimaTrupanController::class, 'searchCategorias']); // Filtro por categoría y subcategoría


//materia prima vidrio
Route::get('/materiaPrimaVidrios', [MateriaPrimaVidrioController::class, 'index']);
Route::post('/materiaPrimaVidrio', [MateriaPrimaVidrioController::class, 'store']);
Route::get('/materiaPrimaVidrio/{id}', [MateriaPrimaVidrioController::class, 'show']);
Route::post('/materiaPrimaVidrio/edit/{id}', [MateriaPrimaVidrioController::class, 'update']);
Route::delete('/materiaPrimaVidrio/{id}', [MateriaPrimaVidrioController::class, 'destroy']);

Route::get('/materiaPrimaVidrios/paginados', [MateriaPrimaVidrioController::class, 'indexPaginado']); // Listado paginado
Route::get('/materiaPrimaVidrios/search', [MateriaPrimaVidrioController::class, 'search']); // Búsqueda general
Route::get('/materiaPrimaVidrios/search-categorias', [MateriaPrimaVidrioController::class, 'searchCategorias']); // Filtro por categoría y subcategoría


// materia prima contorno
Route::get('/materiaPrimaContornos', [MateriaPrimaContornoController::class, 'index']);
Route::post('/materiaPrimaContorno', [MateriaPrimaContornoController::class, 'store']);
Route::get('/materiaPrimaContorno/{id}', [MateriaPrimaContornoController::class, 'show']);
Route::post('/materiaPrimaContorno/edit/{id}', [MateriaPrimaContornoController::class, 'update']);
Route::delete('/materiaPrimaContorno/{id}', [MateriaPrimaContornoController::class, 'destroy']);

Route::get('/materiaPrimaContornos/paginados', [MateriaPrimaContornoController::class, 'indexPaginado']); // Listado paginado
Route::get('/materiaPrimaContornos/search', [MateriaPrimaContornoController::class, 'search']); // Búsqueda general
Route::get('/materiaPrimaContornos/search-categorias', [MateriaPrimaContornoController::class, 'searchCategorias']); // Filtro por categoría y subcategoría


// stock varila 

Route::get('/stockVarillas', [StockVarillaController::class, 'index']); // Obtener todos los registros
Route::post('/stockVarillas', [StockVarillaController::class, 'store']); // Crear un nuevo registro
Route::get('/stockVarilla/{id}', [StockVarillaController::class, 'show']); // Obtener un registro por ID
Route::get('/stockVarillas/porVarilla/{id}', [StockVarillaController::class, 'indexPorVarilla']);
Route::put('/stockVarilla/{id}', [StockVarillaController::class, 'update']); // Actualizar un registro
Route::delete('/stockVarilla/{id}', [StockVarillaController::class, 'destroy']); // Eliminar un registro

// stock vidrio
Route::get('/stockVidrios', [StockVidrioController::class, 'index']); // Obtener todos los registros
Route::post('/stockVidrio', [StockVidrioController::class, 'store']); // Crear un nuevo registro
Route::get('/stockVidrio/{id}', [StockVidrioController::class, 'show']); // Obtener un registro por ID
Route::get('/stockVidrios/porVidrio/{id}', [StockVidrioController::class, 'indexPorVidrio']);
Route::put('/stockVidrio/{id}', [StockVidrioController::class, 'update']); // Actualizar un registro
Route::delete('/stockVidrio/{id}', [StockVidrioController::class, 'destroy']); // Eliminar un registro

//stock trupan
Route::get('/stockTrupans', [StockTrupanController::class, 'index']); // Obtener todos los registros
Route::post('/stockTrupans', [StockTrupanController::class, 'store']); // Crear un nuevo registro
Route::get('/stockTrupans/{id}', [StockTrupanController::class, 'show']); // Obtener un registro por ID
Route::get('/stockTrupans/porTrupan/{id}', [StockTrupanController::class, 'indexPorTrupan']);
Route::put('/stockTrupans/{id}', [StockTrupanController::class, 'update']); // Actualizar un registro
Route::delete('/stockTrupans/{id}', [StockTrupanController::class, 'destroy']); // Eliminar un registro


// Stock de Productos
Route::get  ('/stockProductos',           [StockProductoController::class, 'index']);   // Obtener todos los registros
Route::post ('/stockProducto',           [StockProductoController::class, 'store']);   // Crear un nuevo registro
Route::get  ('/stockProducto/{id}',      [StockProductoController::class, 'show']);    // Obtener un registro por ID
Route::get('/stockProductos/porProducto/{id_producto}', [StockProductoController::class, 'getByProducto']);
Route::put  ('/stockProducto/{id}',      [StockProductoController::class, 'update']);  // Actualizar un registro
Route::delete('/stockProducto/{id}',      [StockProductoController::class, 'destroy']); // Eliminar un registro
//stock de contornos

Route::get('/stockContornos', [StockContornoController::class, 'index']);
Route::post('/stockContorno', [StockContornoController::class, 'store']);
Route::get('/stockContorno/{id}', [StockContornoController::class, 'show']);
Route::put('/stockContorno/{id}', [StockContornoController::class, 'update']);
Route::delete('/stockContorno/{id}', [StockContornoController::class, 'destroy']);
Route::get('/stockContorno/porContorno/{id}', [StockContornoController::class, 'indexPorContorno']);


//cliente
Route::get('/clientes', [ClienteController::class, 'index']); // Obtener todos los registros
Route::get('/clientes/fullName', [ClienteController::class, 'indexFullName']); // Obtener todos los registros con nombre completo
Route::get('/clientes/paginados', [ClienteController::class, 'indexPaginado']); // Obtener registros paginados
Route::get('/clientes/search', [ClienteController::class, 'search']); // Buscar clientes
Route::get('/clientes/searchFullName', [ClienteController::class, 'searchFullName']); // Buscar clientes
Route::get('/clientes/total', [ClienteController::class, 'totalClientes']); // Obtener total de clientes
Route::post('/cliente', [ClienteController::class, 'store']); // Crear un nuevo registro
Route::get('/cliente/{id}', [ClienteController::class, 'show']); // Obtener un registro por ID
Route::put('/cliente/{id}', [ClienteController::class, 'update']); // Actualizar un registro
Route::delete('/cliente/{id}', [ClienteController::class, 'destroy']); // Eliminar un registro

//roles
Route::get('/roles', [RolController::class, 'index']); // Obtener todos los roles
Route::post('/rol', [RolController::class, 'store']); // Crear un nuevo rol
Route::get('/rol/{id}', [RolController::class, 'show']); // Obtener un rol por ID
Route::put('/rol/{id}', [RolController::class, 'update']); // Actualizar un rol
Route::delete('/rol/{id}', [RolController::class, 'destroy']); // Eliminar un rol


//ventas
// venta
Route::get('/ventas', [VentaController::class, 'index']); // Obtener todas las ventas
Route::get('/ventas/paginadas', [VentaController::class, 'indexPaginado']); // Obtener ventas paginadas
Route::get('/ventas/search', [VentaController::class, 'search']); // Buscar ventas
Route::get('/ventas/total', [VentaController::class, 'totalVentas']); // Obtener total de ventas
Route::post('/venta', [VentaController::class, 'store']); // Crear una nueva venta
Route::get('/venta/{id}', [VentaController::class, 'showVentaDetalleProducto']); // Obtener una venta por ID
Route::put('/venta/{id}', [VentaController::class, 'update']); // Actualizar una venta
Route::delete('/venta/{id}', [VentaController::class, 'destroy']); // Eliminar una venta
//venta con detalle
Route::post('/venta/ventasConDetalle', [VentaController::class, 'storeConDetalle']); // Obtener todas las ventas con detalle

Route::post('/venta/ventaDetalles', [VentaController::class, 'store1']);
Route::get('/venta/ventaCompleta/{id}', [VentaController::class, 'getVentaCompleta']);
Route::put('venta/completar/{id}', [VentaController::class, 'completarVenta']);

Route::get('/ventas/resumen-dashboard', [VentaController::class, 'resumenDashboard']);
Route::get('/ventas/detallado', [VentaController::class, 'ventasPorFechaDetallado']);

Route::get('/ventas/cierrecaja', [VentaController::class, 'resumenDelDia']);

//formas de pago
use App\Http\Controllers\FormaDePagoController;

Route::get('/formasPago', [FormaDePagoController::class, 'index']);
Route::post('/formasPago', [FormaDePagoController::class, 'store']);
Route::get('/formaPago/{id}', [FormaDePagoController::class, 'show']);
Route::put('/formasPago/{id}', [FormaDePagoController::class, 'update']);
Route::delete('/formaPago/{id}', [FormaDePagoController::class, 'destroy']);

use App\Http\Controllers\PagoController;

Route::get('/pagos', [PagoController::class, 'index']);
Route::post('/pago', [PagoController::class, 'store']);
Route::post('/pago/completar', [PagoController::class, 'completarPago']);

Route::get('/pago/{id}', [PagoController::class, 'show']);
Route::put('/pago/{id}', [PagoController::class, 'update']);
Route::delete('/pago/{id}', [PagoController::class, 'destroy']);

// Caja
use App\Http\Controllers\CajaController;

Route::get('/cajas', [CajaController::class, 'index']);
Route::post('/caja', [CajaController::class, 'store']);
Route::get('/caja/{id}', [CajaController::class, 'show']);
//Route::put('/cajas/{id}', [CajaController::class, 'update']);
Route::delete('/cajas/{id}', [CajaController::class, 'destroy']);
Route::get('/caja/fecha/{fecha}', [CajaController::class, 'obtenerPorFecha']); 
Route::get('/caja/mes/{mes}', [CajaController::class, 'cajaPorMes']);
//pdfss
Route::get('/caja/html/{fecha}', [CajaController::class, 'htmlPorDia'])->name('html.caja.dia');
Route::get('/cajas/html/mes/{mes}', [CajaController::class, 'htmlPorMes'])->name('html.cajas.mes');

Route::get('/caja/pdf/{fecha}', [CajaController::class, 'pdfPorDia'])->name('pdf.caja.dia');
Route::get('/cajas/pdf/{mes}', [CajaController::class, 'pdfPorMes'])->name('pdf.cajas.mes');




//detalle venta producto
use App\Http\Controllers\DetalleVentaProductoController;

Route::get('/detalle-venta-productos', [DetalleVentaProductoController::class, 'index']); // Listar todos con paginación opcional
Route::get('/detalle-venta-producto/{id}', [DetalleVentaProductoController::class, 'show']); // Ver uno por ID
Route::post('/detalle-venta-producto', [DetalleVentaProductoController::class, 'store']); // Crear uno nuevo
Route::put('/detalle-venta-producto/{id}', [DetalleVentaProductoController::class, 'update']); // Actualizar
Route::delete('/detalle-venta-producto/{id}', [DetalleVentaProductoController::class, 'destroy']); // Eliminar



use App\Http\Controllers\DetalleVentaPersonalizadaController;
// Detalle Venta Personalizada
Route::get('/detalle-venta-personalizadas', [DetalleVentaPersonalizadaController::class, 'index']);
Route::get('/detalle-venta-personalizada/{id}', [DetalleVentaPersonalizadaController::class, 'show']);
Route::post('/detalle-venta-personalizada', [DetalleVentaPersonalizadaController::class, 'store']);
Route::put('/detalle-venta-personalizada/{id}', [DetalleVentaPersonalizadaController::class, 'update']);
Route::delete('/detalle-venta-personalizada/{id}', [DetalleVentaPersonalizadaController::class, 'destroy']);
Route::get('/detalle-venta-personalizadas/venta/{id_venta}', [DetalleVentaPersonalizadaController::class, 'getByVenta']);
Route::get('/detalle-venta-personalizadas/varilla', [DetalleVentaPersonalizadaController::class, 'varilla']); // Listar con paginación
Route::get('/detalle-venta-personalizadas/lamina', [DetalleVentaPersonalizadaController::class, 'lamina']); // Listar con paginación

use App\Http\Controllers\MaterialesVentaPersonalizadaController;

Route::get('/materiales-venta-personalizadas', [MaterialesVentaPersonalizadaController::class, 'index']); // Listar todos con paginación opcional
Route::get('/materiales-venta-personalizada/{materialesVentaPersonalizada}', [MaterialesVentaPersonalizadaController::class, 'show']); // Ver uno por ID
Route::post('/materiales-venta-personalizada', [MaterialesVentaPersonalizadaController::class, 'store']); // Crear uno nuevo
Route::put('/materiales-venta-personalizada/{materialesVentaPersonalizada}', [MaterialesVentaPersonalizadaController::class, 'update']); // Actualizar
Route::delete('/materiales-venta-personalizada/{materialesVentaPersonalizada}', [MaterialesVentaPersonalizadaController::class, 'destroy']); // Eliminar

// materiales de venta perzonalizda
Route::get('/materiales-venta-personalizadas', [MaterialesVentaPersonalizadaController::class, 'index']);
Route::post('/material-venta-personalizada', [MaterialesVentaPersonalizadaController::class, 'store']);
Route::get('/material-venta-personalizada/{materialesVentaPersonalizada}', [MaterialesVentaPersonalizadaController::class, 'show']);
Route::put('/material-venta-personalizada/{materialesVentaPersonalizada}', [MaterialesVentaPersonalizadaController::class, 'update']);
Route::delete('/material-venta-personalizada/{materialesVentaPersonalizada}', [MaterialesVentaPersonalizadaController::class, 'destroy']);
Route::get('/materiales-venta-personalizada/detalle/{detalleVP_id}', [MaterialesVentaPersonalizadaController::class, 'materialesPorVentaVp']);

//todo de productos
Route::get('/materias-primas-paginado', [CotizadorController::class, 'indexPaginadoGeneralPorMasReciente']);
Route::get('/materias-primas-search', [CotizadorController::class, 'searchPaginadoGeneral']);
//recibos
use App\Http\Controllers\GestionRecibosController;

Route::get('/recibo/html/{id}', [GestionRecibosController::class, 'getHtml'])->name('recibo.html');
Route::get('/recibo/pdf/{id}', [GestionRecibosController::class, 'getPdf'])->name('recibo.pdf');
Route::get('/recibo/{id}', [GestionRecibosController::class, 'show']);


//auth
Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::get('/cotizar',[CotizadorController::class, 'calcular']);

Route::middleware('auth:sanctum')->group(function(){
    Route::get('/logout', [AuthController::class, 'logout']);
});

//user
Route::get('/users', [UserController::class, 'index']); // Obtener todos los usuarios
//Route::post('/user', [UserController::class, 'store']); // Crear un nuevo usuario
Route::get('/user/{id}', [UserController::class, 'show']); // Obtener un usuario por ID
Route::put('/user/{id}', [UserController::class, 'update']); // Actualizar un usuario
Route::delete('/user/{id}', [UserController::class, 'destroy']); // Eliminar un usuario

// Funciones Generales
Route::get('/estadisticas/clientesNuevos', [FuncionesGeneralesController::class, 'ClientesNuevos']);
//4 del cuadro de estadisticas
//Route::get('/clientes/total', [ClienteController::class, 'totalClientes']);
//venta completa de prodcuto y marcos

// use App\Http\Controllers\GestiionVentas\GestionVenta;

// Ruta para crear venta completa (productos + cuadros personalizados)
use App\Http\Controllers\GestionVentas\GestionVentaController;
Route::post('/ventaProductoMarco', [GestionVentaController::class, 'crearVentaCompleta']);
// En routes/api.php
Route::get('/ventaProductoMarco/{id}', [GestionVentaController::class, 'obtenerVentaCompleta']);
Route::get('/ventaProductoMarco', [GestionVentaController::class, 'obtenerVentas']);

Route::delete('/ventaProductoMarco/{id}', [GestionVentaController::class, 'eliminarVenta']);
Route::get('/ventaProductoMarco/{id}/verificar-eliminacion', [GestionVentaController::class, 'verificarEliminacionVenta']);