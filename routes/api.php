<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\CotizadorController;
use App\Http\Controllers\FuncionesGeneralesController;
use App\Http\Controllers\LugarController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\StockProductoController;
use App\Http\Controllers\SubCategoriaController;
use App\Http\Controllers\SucursalController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FormaDePagoController;
use App\Http\Controllers\GestionVentas\GestionVentaController;

// Lugares
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

// categorias
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

// producto

Route::get('/productos', [ProductoController::class, 'index']);
Route::post('/producto', [ProductoController::class, 'store']);
Route::get('/producto/{id}', [ProductoController::class, 'show']);
Route::post('/producto/edit/{id}', [ProductoController::class, 'update']);
Route::delete('/producto/{id}', [ProductoController::class, 'destroy']);
Route::get('/productos/search', [ProductoController::class, 'search']);
Route::get('/productos/search-categorias', [ProductoController::class, 'searchCategorias']);
Route::get('/productos/paginados', [ProductoController::class, 'indexPaginado']);

// Stock de Productos
Route::get('/stockProductos', [StockProductoController::class, 'index']);   // Obtener todos los registros
Route::post('/stockProducto', [StockProductoController::class, 'store']);   // Crear un nuevo registro
Route::get('/stockProducto/{id}', [StockProductoController::class, 'show']);    // Obtener un registro por ID
Route::get('/stockProductos/porProducto/{id_producto}', [StockProductoController::class, 'getByProducto']);
Route::put('/stockProducto/{id}', [StockProductoController::class, 'update']);  // Actualizar un registro
Route::delete('/stockProducto/{id}', [StockProductoController::class, 'destroy']); // Eliminar un registro

// cliente
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

// roles
Route::get('/roles', [RolController::class, 'index']); // Obtener todos los roles
Route::post('/rol', [RolController::class, 'store']); // Crear un nuevo rol
Route::get('/rol/{id}', [RolController::class, 'show']); // Obtener un rol por ID
Route::put('/rol/{id}', [RolController::class, 'update']); // Actualizar un rol
Route::delete('/rol/{id}', [RolController::class, 'destroy']); // Eliminar un rol

// ventas

Route::get('/ventas', [VentaController::class, 'index']); // Obtener todas las ventas
Route::get('/ventas/paginadas', [VentaController::class, 'indexPaginado']); // Obtener ventas paginadas
Route::get('/ventas/search', [VentaController::class, 'search']); // Buscar ventas
Route::get('/ventas/total', [VentaController::class, 'totalVentas']); // Obtener total de ventas
Route::post('/venta', [VentaController::class, 'store']); // Crear una nueva venta
Route::get('/venta/{id}', [VentaController::class, 'showVentaDetalleProducto']); // Obtener una venta por ID
Route::put('/venta/{id}', [VentaController::class, 'update']); // Actualizar una venta
Route::delete('/venta/{id}', [VentaController::class, 'destroy']); // Eliminar una venta
// venta con detalle
Route::post('/venta/ventasConDetalle', [VentaController::class, 'storeConDetalle']); // Obtener todas las ventas con detalle

Route::post('/venta/ventaDetalles', [VentaController::class, 'store1']);
Route::get('/venta/ventaCompleta/{id}', [VentaController::class, 'getVentaCompleta']);
Route::get('/venta/ventaCompletaAdministrativa/{id}', [VentaController::class, 'getVentaCompletaAdministrativa']);
Route::put('venta/completar/{id}', [VentaController::class, 'completarVenta']);

Route::get('/ventas/resumen-dashboard', [VentaController::class, 'resumenDashboard']);
Route::get('/ventas/detallado', [VentaController::class, 'ventasPorFechaDetallado']);

Route::get('/ventas/cierrecaja', [VentaController::class, 'resumenDelDia']);

// formas de pago


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
Route::get('/cajas/{idSucursal}', [CajaController::class, 'getAllSucursal']);

Route::post('/caja', [CajaController::class, 'store']);
Route::get('/caja/{id}', [CajaController::class, 'show']);

Route::delete('/cajas/{id}', [CajaController::class, 'destroy']);

Route::get('/caja/fecha/{fecha}', [CajaController::class, 'obtenerPorFecha']);
Route::get('/caja/mes/{mes}', [CajaController::class, 'cajaPorMes']);
// pdfss
Route::get('/caja/html/{fecha}', [CajaController::class, 'htmlPorDia'])->name('html.caja.dia');
Route::get('/cajas/html/mes/{mes}', [CajaController::class, 'htmlPorMes'])->name('html.cajas.mes');

Route::get('/caja/pdf/{fecha}', [CajaController::class, 'pdfPorDia'])->name('pdf.caja.dia');
Route::get('/caja/pdf/{fecha}/{idSucursal}', [CajaController::class, 'pdfDiaSucursal'])->name('pdf.caja.dia.sucursal');
Route::get('/caja/pdf/mes/{fechaMes}', [CajaController::class, 'cajaPorMes'])->name('pdf.caja.mes');

// Route::get('/pruebaController', [CajaController::class, 'errotest']);
Route::get('/pruebaController/{fechaMes}', [CajaController::class, 'cajaPorMes']);

// detalle venta producto
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

// todo de productos
Route::get('/materias-primas-paginado', [CotizadorController::class, 'indexPaginadoGeneralPorMasReciente']);
Route::get('/materias-primas-search', [CotizadorController::class, 'searchPaginadoGeneral']);
// recibos
use App\Http\Controllers\GestionRecibosController;

Route::get('/recibo/html/{id}', [GestionRecibosController::class, 'getHtml'])->name('recibo.html');
Route::get('/recibo/pdf/{id}', [GestionRecibosController::class, 'getPdf'])->name('recibo.pdf');
Route::get('/recibo/administrativo/html/{id}', [GestionRecibosController::class, 'getAdministrativoHtml'])->name('recibo.admin.html');
Route::get('/recibo/administrativo/pdf/{id}', [GestionRecibosController::class, 'getAdministrativoPdf'])->name('recibo.admin.pdf');
Route::get('/recibo/{id}', [GestionRecibosController::class, 'show']);

// auth
Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::get('/cotizar', [CotizadorController::class, 'calcular']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/logout', [AuthController::class, 'logout']);
});


Route::get('/users', [UserController::class, 'index']); 
Route::get('/user/{id}', [UserController::class, 'show']);
Route::put('/user/{id}', [UserController::class, 'update']);
Route::delete('/user/{id}', [UserController::class, 'destroy']);


Route::get('/estadisticas/clientesNuevos', [FuncionesGeneralesController::class, 'ClientesNuevos']);
Route::post('/ventaProductoMarco', [GestionVentaController::class, 'crearVentaCompleta']);
Route::post('/ventaProductoMarco/SimularVenta', [GestionVentaController::class, 'SimularVenta']);

Route::delete('/ventaProductoMarco/devolucion/{id}', [GestionVentaController::class, 'crearDevolucion']);


Route::get('/ventaProductoMarco/{id}', [GestionVentaController::class, 'obtenerVentaCompleta']);
Route::get('/ventaProductoMarco', [GestionVentaController::class, 'obtenerVentas']);

Route::delete('/ventaProductoMarco/{id}', [GestionVentaController::class, 'eliminarVenta']);
Route::get('/ventaProductoMarco/{id}/verificar-eliminacion', [GestionVentaController::class, 'verificarEliminacionVenta']);
