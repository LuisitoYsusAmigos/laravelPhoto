<?php

namespace App\Http\Controllers\GestionVentas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\Venta;
use App\Models\Pago;

class GestionVentaController extends Controller
{
    protected $gestionProductos;
    protected $gestionMarcos;

    public function __construct()
    {
        $this->gestionProductos = new GestionProductosController();
        $this->gestionMarcos = new GestionMarcosController();
    }
    

    public function crearVentaCompleta(Request $request)
    {
        // Validación básica
        $validator = Validator::make($request->all(), [
            'pago' => 'required|numeric|min:0',
            'idCliente' => 'required|exists:clientes,id',
            'idSucursal' => 'required|exists:sucursal,id',
            'idFormaPago' => 'required|exists:forma_de_pagos,id',
            'idUsuario' => 'required|exists:users,id',
            'factorPrecioVenta' => 'nullable|numeric|min:0',
            'fechaEntrega' => 'nullable|date',
            'descuento' => 'nullable|integer|min:0',
            'entregado' => 'nullable|boolean',

            'detalles' => 'nullable|array',
            'detalles.*.idProducto' => 'required_with:detalles|integer',
            'detalles.*.cantidad' => 'required_with:detalles|integer|min:1',

            'cuadros' => 'nullable|array',
            'cuadros.*.lado_a' => 'required_with:cuadros|integer|min:1',
            'cuadros.*.lado_b' => 'required_with:cuadros|integer|min:1',
            'cuadros.*.cantidad' => 'required_with:cuadros|integer|min:1',
            'cuadros.*.id_materia_prima_varillas' => 'nullable|integer',
            'cuadros.*.id_materia_prima_trupans' => 'nullable|integer',
            'cuadros.*.id_materia_prima_vidrios' => 'nullable|integer',
            'cuadros.*.id_materia_prima_contornos' => 'nullable|integer',
        ]);
        // define una varible que sea factorprecioVenta que sea igual a la recivida en el request
        $factorPrecioVenta = $request->input('factorPrecioVenta') ?? 1;
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Faltan datos o datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }


        $request->merge([
            'saldo' => $request->input('pago')
        ]);



        if (empty($request->detalles) && empty($request->cuadros)) {
            return response()->json([
                'error' => 'Debe incluir al menos productos o cuadros personalizados'
            ], 400);
        }

        if (!empty($request->cuadros)) {
            foreach ($request->cuadros as $index => $cuadro) {
                if (empty($cuadro['id_materia_prima_varillas'])) {
                    return response()->json([
                        'error' => "Debe proporcionar una varilla válida para hacer el cálculo correcto del material en el cuadro #" . ($index + 1)
                    ], 400);
                }
                //calcular tamaño externo del marco
                $medidasExternas = $this->gestionMarcos->obtenerMarcoExterno($request->cuadros);

                $cuadros[$index]['lado_a'] = $medidasExternas['lado_a'];
                $cuadros[$index]['lado_b'] = $medidasExternas['lado_b'];
                $cuadros[$index]['id_materia_prima_varillas'] = $cuadro['id_materia_prima_varillas'];
                
                //quiero colocar un if si mandaron un id materia prima contorno hacer un dd si mando y si no mando un dd no mando
                if(!empty($cuadros[$index]['id_materia_prima_contornos'])){
                    $cuadros[$index]['id_materia_prima_contornos'] = $cuadro['id_materia_prima_contornos'];
                }
                if(!empty($cuadros[$index]['id_materia_prima_trupans'])){
                    $cuadros[$index]['id_materia_prima_trupans'] = $cuadro['id_materia_prima_trupans'];
                }     
                if(!empty($cuadros[$index]['id_materia_prima_vidrios'])){
                    $cuadros[$index]['id_materia_prima_vidrios'] = $cuadro['id_materia_prima_vidrios'];
                }
                if(!empty($cuadros[$index]['cantidad'])){
                    $cuadros[$index]['cantidad'] = $cuadro['cantidad'];
                }
                
            }
            
        }
        
        if (!empty($request->detalles)) {
            $validacionProductos = $this->gestionProductos->verificarDisponibilidad($request->detalles);

            $errores = array_filter($validacionProductos, fn($p) => $p['disponible'] === false);

            if (!empty($errores)) {
                return response()->json([
                    'message' => 'Errores en los productos',
                    'detalles' => $errores
                ], 400);
            }
        }

        //  Validar marcos y stock ANTES de crear la venta
        if (!empty($request->cuadros)) {
            $validacionMarcos = $this->gestionMarcos->verificarDisponibilidadMarcos($request->cuadros);

            $errores = array_filter($validacionMarcos, fn($c) => $c['valido'] === false);

            if (!empty($errores)) {
                return response()->json([
                    'message' => 'Errores en los cuadros personalizados',
                    'detalles' => $errores
                ], 400);
            }
        }

        DB::beginTransaction();

        try {
            // Crear la venta
            $venta = $this->crearVentaBase($request);

            //  Inicializar totales separados
            $totalProductos = 0;
            $totalCuadros = 0;

            if (!empty($request->detalles)) {
                $totalProductos = $this->gestionProductos->procesarProductos($venta, $request->detalles);
            }

            if (!empty($request->cuadros)) {
                $resultadoMarcos = $this->gestionMarcos->procesarMarcos($venta, $request->cuadros,$factorPrecioVenta);
                $totalCuadros = $resultadoMarcos['total'];
            }
            $totalVenta = $totalProductos + $totalCuadros;

            // ✅ Actualizar con totales separados
            $this->actualizarTotalesVenta($venta, $totalProductos, $totalCuadros, $totalVenta);
            
            $this->procesarPago($venta, $request->saldo, $totalVenta, $request->idFormaPago, $request->entregado,$request->descuento);
            $venta = $this->cargarRelacionesVenta($venta);

            DB::commit();

            return response()->json([
                'message' => 'Venta completa creada exitosamente',
                'venta' => $this->formatearRespuestaVenta($venta),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function crearVentaBase(Request $request)
    {   
        return Venta::create([
            'saldo' => $request->saldo,
            'idCliente' => $request->idCliente,
            'idSucursal' => $request->idSucursal,
            'idUsuario' => $request->idUsuario,
            'recogido' => false,
            'fecha' => now(),
            'fechaEntrega' => $request->fechaEntrega ?? null,
            'descuento' => $request->descuento ?? 0,
        ]);
    }

    private function actualizarTotalesVenta($venta, $totalProductos, $totalCuadros, $totalVenta)
    {
        $venta->update([
            'precioProducto' => $totalProductos,
            'precioPerzonalizado' => $totalCuadros,
            'precioTotal' => $totalVenta,
        ]);
    }

    private function procesarPago($venta, $saldo, $precioTotal, $idFormaPago, $entregado, $descuento)
    {
        if($descuento!=null){
            //dd("se envio descuento");
            if($descuento>$precioTotal){
                throw new \Exception("El descuento no puede ser mayor al precio total de la venta");
            }else{
                $precioTotal = $precioTotal - $descuento;
                // Actualizar el precio total en la base de datos para que refleje el descuento
                $venta->update(['precioTotal' => $precioTotal]);
                //dd("descuento aplicado");
            }
        }
        if ($saldo > $precioTotal) {
            $exceso = $saldo - $precioTotal;
            throw new \Exception("Hay un excedente de $exceso unidades en el saldo/pagos");
        }

        if ($saldo > 0) {
            Pago::create([
                'idVenta' => $venta->id,
                'idFormaPago' => $idFormaPago,
                'monto' => $saldo,
                'fecha' => now()->toDateString(),
            ]);
        } elseif ($saldo == 0 &&  $entregado == false) {
            $venta->update([
                'recogido' => false,
                'saldo' => 0
            ]);
        } elseif ($saldo == 0) {
            $venta->update([
                'recogido' => true,
                'saldo' => $precioTotal
            ]);

            Pago::create([
                'idVenta' => $venta->id,
                'idFormaPago' => $idFormaPago,
                'monto' => $precioTotal,
                'fecha' => now()->toDateString(),
            ]);
        }
    }

    private function cargarRelacionesVenta($venta)
    {
        return $venta->load([
            'cliente',
            'sucursal',
            'detalleVentaProductos',
            'detalleVentaPersonalizadas.materiaPrimaVarilla',
            'detalleVentaPersonalizadas.materiaPrimaTrupan',
            'detalleVentaPersonalizadas.materiaPrimaVidrio',
            'detalleVentaPersonalizadas.materiaPrimaContorno'
        ]);
    }

    // ... resto de métodos (obtenerVentaCompleta, obtenerVentas, eliminarVenta, verificarEliminacionVenta, formatearRespuestaVenta)



    /**
     * Obtiene una venta completa por ID
     */
    public function obtenerVentaCompleta($id)
    {

        try {
            $venta = Venta::with([
                'cliente',
                'sucursal',
                'detalleVentaProductos.producto',
                'detalleVentaPersonalizadas.materiaPrimaVarilla',
                'detalleVentaPersonalizadas.materiaPrimaTrupan',
                'detalleVentaPersonalizadas.materiaPrimaVidrio',
                'detalleVentaPersonalizadas.materiaPrimaContorno',
                'detalleVentaPersonalizadas.materialesVentaPersonalizadas'
            ])->find($id);

            if (!$venta) {
                return response()->json([
                    'error' => 'Venta no encontrada'
                ], 404);
            }

            // Obtener el idFormaPago del primer pago
            $idFormaPago = DB::table('pagos')
                ->where('idVenta', $id)
                ->orderBy('id', 'ASC')
                ->value('idFormaPago');
            if ($idFormaPago === null) {
                $idFormaPago = 0; // o cualquier valor que indique "sin forma de pago"
            }
            // Formatear la respuesta y agregar id_forma_pago
            $ventaFormateada = $this->formatearRespuestaVenta($venta);
            $ventaFormateada['id_forma_pago'] = $idFormaPago;

            return response()->json([
                'message' => 'Venta obtenida exitosamente',
                'venta' => $ventaFormateada
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtiene múltiples ventas con filtros opcionales
     */
    public function obtenerVentas(Request $request)
    {
        try {
            // Construir consulta base
            $query = Venta::query();

            // Filtros opcionales
            if ($request->has('idCliente')) {
                $query->where('idCliente', $request->idCliente);
            }

            if ($request->has('idSucursal')) {
                $query->where('idSucursal', $request->idSucursal);
            }

            if ($request->has('fecha_desde')) {
                $query->whereDate('fecha', '>=', $request->fecha_desde);
            }

            if ($request->has('fecha_hasta')) {
                $query->whereDate('fecha', '<=', $request->fecha_hasta);
            }

            if ($request->has('recogido')) {
                $recogidoValue = $request->recogido;
                // Convertir string a booleano correctamente
                if ($recogidoValue === 'true' || $recogidoValue === '1' || $recogidoValue === 1) {
                    $query->where('recogido', true);
                } elseif ($recogidoValue === 'false' || $recogidoValue === '0' || $recogidoValue === 0) {
                    $query->where('recogido', false);
                }
            }

            // Parámetros de paginación
            $page = (int) $request->input('page', 1);
            $perPage = (int) $request->input('per_page', 15);

            // Validar valores
            $perPage = max(1, min($perPage, 100));
            $page = max(1, $page);

            // Obtener total de elementos
            $totalItems = $query->count();

            // Si no hay resultados, devolver 404 con descripción del filtro
            if ($totalItems === 0) {
                $filtrosAplicados = [];

                if ($request->has('idCliente')) {
                    $filtrosAplicados[] = "Cliente ID: {$request->idCliente}";
                }
                if ($request->has('idSucursal')) {
                    $filtrosAplicados[] = "Sucursal ID: {$request->idSucursal}";
                }
                if ($request->has('fecha_desde')) {
                    $filtrosAplicados[] = "Desde: {$request->fecha_desde}";
                }
                if ($request->has('fecha_hasta')) {
                    $filtrosAplicados[] = "Hasta: {$request->fecha_hasta}";
                }
                if ($request->has('recogido')) {
                    $estadoRecogido = $request->recogido === 'true' ? 'recogidas' : 'no recogidas';
                    $filtrosAplicados[] = "Estado: ventas {$estadoRecogido}";
                }

                $descripcionFiltros = empty($filtrosAplicados)
                    ? "todas las ventas"
                    : implode(', ', $filtrosAplicados);

                return response()->json([
                    'error' => "No se encontraron ventas con los filtros aplicados: {$descripcionFiltros}"
                ], 404);
            }

            $totalPages = $perPage > 0 ? ceil($totalItems / $perPage) : 1;

            // Obtener ventas con paginación manual
            $ventas = $query->orderBy('created_at', 'desc')
                ->skip(($page - 1) * $perPage)
                ->take($perPage)
                ->with([
                    'cliente',
                    'sucursal',
                    'detalleVentaProductos',
                    'detalleVentaPersonalizadas.materiaPrimaVarilla',
                    'detalleVentaPersonalizadas.materiaPrimaTrupan',
                    'detalleVentaPersonalizadas.materiaPrimaVidrio',
                    'detalleVentaPersonalizadas.materiaPrimaContorno'
                ])
                ->get();

            // Transformar elementos
            $ventasTransformadas = $ventas->map(function ($venta) {
                // Obtener el idFormaPago del primer pago
                $idFormaPago = DB::table('pagos')
                    ->where('idVenta', $venta->id)
                    ->orderBy('id', 'ASC')
                    ->value('idFormaPago');
                if ($idFormaPago === null) {
                    $idFormaPago = 0; // o cualquier valor que indique "sin forma de pago"
                }
                // Armar la respuesta formateada
                $ventaFormateada = $this->formatearRespuestaVenta($venta);
                $ventaFormateada['id_forma_pago'] = $idFormaPago;

                return $ventaFormateada;
            });

            return response()->json([
                'message' => 'Ventas obtenidas exitosamente',
                'ventas' => $ventasTransformadas,
                'pagination' => [
                    'current_page' => $page,
                    'last_page' => $totalPages,
                    'per_page' => $perPage,
                    'total' => $totalItems,
                    'from' => $totalItems > 0 ? (($page - 1) * $perPage) + 1 : null,
                    'to' => $totalItems > 0 ? min($page * $perPage, $totalItems) : null,
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener las ventas: ' . $e->getMessage()
            ], 500);
        }
    }


    // ============== MÉTODO DE ELIMINACIÓN DE VENTA ==============

    /**
     * Elimina completamente una venta y devuelve todo el stock
     * 
     * @param int $idVenta ID de la venta a eliminar
     * @return \Illuminate\Http\JsonResponse
     */
    public function eliminarVenta($idVenta)
    {
        DB::beginTransaction();

        try {
            // 1. Verificar que la venta existe
            $venta = Venta::with([
                'detalleVentaProductos',
                'detalleVentaPersonalizadas.materialesVentaPersonalizada'
            ])->find($idVenta);

            if (!$venta) {
                return response()->json([
                    'error' => 'Venta no encontrada'
                ], 404);
            }

            // 2. Obtener información para el log de respuesta
            $infoEliminacion = [
                'venta_id' => $venta->id,
                'fecha_venta' => $venta->fecha,
                'total_venta' => $venta->precioTotal,
                'productos_info' => []
            ];

            // 3. Procesar productos regulares si existen
            if ($venta->detalleVentaProductos->count() > 0) {
                // Obtener info antes de eliminar
                $infoEliminacion['productos_info'] = $this->gestionProductos->obtenerInfoDevolucionProductos($idVenta);

                // Devolver stock de productos
                $this->gestionProductos->devolverStockProductos($venta->detalleVentaProductos);

                // Eliminar detalles de productos
                $this->gestionProductos->eliminarDetallesProductos($idVenta);
            }

            // 4. Por ahora, si hay marcos personalizados, mostrar error
            if ($venta->detalleVentaPersonalizadas->count() > 0) {
                return response()->json([
                    'error' => 'No se pueden eliminar ventas con marcos personalizados. Esta funcionalidad estará disponible próximamente.'
                ], 400);
            }

            // 5. Eliminar pagos asociados
            $pagosEliminados = Pago::where('idVenta', $idVenta)->delete();

            // 6. Eliminar la venta principal
            $venta->delete();

            // 7. Log de auditoría
            Log::info("Venta eliminada completamente", [
                'venta_id' => $idVenta,
                'pagos_eliminados' => $pagosEliminados,
                'info_eliminacion' => $infoEliminacion
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Venta eliminada exitosamente',
                'venta_eliminada' => [
                    'id' => $idVenta,
                    'fecha_original' => $infoEliminacion['fecha_venta'],
                    'total_original' => $infoEliminacion['total_venta'],
                    'productos_devueltos' => $infoEliminacion['productos_info']['total_productos_diferentes'] ?? 0,
                    'pagos_eliminados' => $pagosEliminados
                ]
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("Error al eliminar venta", [
                'venta_id' => $idVenta,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error al eliminar la venta: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verifica si una venta puede ser eliminada
     * 
     * @param int $idVenta ID de la venta
     * @return \Illuminate\Http\JsonResponse
     */
    public function verificarEliminacionVenta($idVenta)
    {
        try {
            $venta = Venta::with([
                'detalleVentaProductos',
                'detalleVentaPersonalizadas',
                'pagos'
            ])->find($idVenta);

            if (!$venta) {
                return response()->json([
                    'puede_eliminar' => false,
                    'motivo' => 'Venta no encontrada'
                ], 404);
            }

            // Validar si tiene marcos personalizados
            if ($venta->detalleVentaPersonalizadas->count() > 0) {
                return response()->json([
                    'puede_eliminar' => false,
                    'motivo' => 'No se pueden eliminar ventas con marcos personalizados. Esta funcionalidad estará disponible próximamente.'
                ], 400);
            }

            $productosCount = $venta->detalleVentaProductos->count();
            $pagosCount = $venta->pagos->count();

            return response()->json([
                'puede_eliminar' => true,
                'venta_info' => [
                    'id' => $venta->id,
                    'fecha' => $venta->fecha,
                    'total' => $venta->precioTotal,
                    'cliente_id' => $venta->idCliente,
                    'productos_count' => $productosCount,
                    'pagos_count' => $pagosCount,
                    'recogido' => $venta->recogido
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'puede_eliminar' => false,
                'motivo' => 'Error al verificar: ' . $e->getMessage()
            ], 500);
        }
    }

    private function formatearRespuestaVenta($venta)
    {
        return [
            'id' => $venta->id,
            'saldo' => $venta->saldo,
            'idCliente' => $venta->idCliente,
            'idSucursal' => $venta->idSucursal,
            'idUsuario' => $venta->idUsuario,
            'recogido' => $venta->recogido,
            'fecha' => $venta->fecha,
            'fechaEntrega' => $venta->fechaEntrega,
            'saldoFalante' => $venta->precioTotal - $venta->saldo,
            'precioProducto' => $venta->precioProducto,
            'precioPerzonalizado' => $venta->precioPerzonalizado, // ← Agregado
            'descuento' => $venta->descuento,
            'precioTotal' => $venta->precioTotal,
            'created_at' => $venta->created_at,
            'updated_at' => $venta->updated_at,
            'cliente' => $venta->cliente,
            'sucursal' => $venta->sucursal,
            'detalle_venta_productos' => $venta->detalleVentaProductos->map(function($detalle) {
                return [
                    'id' => $detalle->id,
                    'cantidad' => $detalle->cantidad,
                    'precio' => $detalle->precio,
                    'idVenta' => $detalle->idVenta,
                    'idProducto' => $detalle->idProducto,
                    'producto' => $detalle->producto,
                    'id_stock_producto' => $detalle->id_stock_producto,
                    'nombreProducto' => optional($detalle->producto)->nombre,
                ];
            }),
            'detalle_venta_personalizadas' => $venta->detalleVentaPersonalizadas->map(function($detalle) {
                $detalleArray = $detalle->toArray();
                $detalleArray['total'] = $detalle->materialesVentaPersonalizadas->sum('precio_unitario');
                return $detalleArray;
            }),
        ];
    }

    public function consultaPrecioRealMarco(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'factorPrecioVenta' => 'nullable|numeric|min:0',
            'descuento' => 'nullable|integer|min:0',
            'cuadros' => 'required|array',
            'cuadros.*.lado_a' => 'required_with:cuadros|integer|min:1',
            'cuadros.*.lado_b' => 'required_with:cuadros|integer|min:1',
            'cuadros.*.cantidad' => 'required_with:cuadros|integer|min:1',
            'cuadros.*.id_materia_prima_varillas' => 'nullable|integer',
            'cuadros.*.id_materia_prima_trupans' => 'nullable|integer',
            'cuadros.*.id_materia_prima_vidrios' => 'nullable|integer',
            'cuadros.*.id_materia_prima_contornos' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Faltan datos o datos inválidos',
                'errors' => $validator->errors()
            ], 400);
        }

        $factorPrecioVenta = $request->input('factorPrecioVenta') ?? 1;
        $descuento = $request->input('descuento') ?? 0;
        $cuadros = $request->input('cuadros');

        try {
            foreach ($cuadros as $index => $cuadro) {
                if (empty($cuadro['id_materia_prima_varillas'])) {
                    return response()->json([
                        'error' => "Debe proporcionar una varilla válida para hacer el cálculo correcto del material en el cuadro #" . ($index + 1)
                    ], 400);
                }

                // Calcular tamaño externo del marco
                $medidasExternas = $this->gestionMarcos->obtenerMarcoExterno($cuadros);

                $cuadros[$index]['lado_a'] = $medidasExternas['lado_a'];
                $cuadros[$index]['lado_b'] = $medidasExternas['lado_b'];
            }

            // Validar marcos (disponibilidad de stock)
            $validacionMarcos = $this->gestionMarcos->verificarDisponibilidadMarcos($cuadros);
            $errores = array_filter($validacionMarcos, fn($c) => $c['valido'] === false);

            if (!empty($errores)) {
                return response()->json([
                    'message' => 'Errores en los cuadros personalizados (Stock no disponible)',
                    'detalles' => $errores
                ], 400);
            }

            // Simular precio
            $resultadoMarcos = $this->gestionMarcos->simularPrecioMarco($cuadros, $factorPrecioVenta);
            $totalCuadros = $resultadoMarcos['total'];

            $precioTotal = $totalCuadros - $descuento;
            if ($precioTotal < 0) $precioTotal = 0;

            return response()->json([
                'message' => 'Cálculo de precio exitoso',
                'precioCalculadoSinDescuento' => $totalCuadros,
                'descuentoAplicado' => $descuento,
                'precioTotal' => $precioTotal,
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
