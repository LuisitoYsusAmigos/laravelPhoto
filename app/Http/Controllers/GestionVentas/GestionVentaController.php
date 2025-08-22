<?php

namespace App\Http\Controllers\GestionVentas;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        // Validación completa
        $validator = Validator::make($request->all(), [
            // Campos básicos de venta
            'saldo' => 'required|numeric|min:0',
            'idCliente' => 'required|exists:clientes,id',
            'idSucursal' => 'required|exists:sucursal,id',
            'idFormaPago' => 'required|exists:forma_de_pagos,id',
            'idUsuario' => 'required|exists:users,id',
            
            // Detalles de productos (opcional)
            'detalles' => 'nullable|array',
            'detalles.*.idProducto' => 'required_with:detalles|exists:productos,id',
            'detalles.*.cantidad' => 'required_with:detalles|integer|min:1',
            
            // Cuadros personalizados (opcional)
            'cuadros' => 'nullable|array',
            'cuadros.*.lado_a' => 'required_with:cuadros|integer|min:1',
            'cuadros.*.lado_b' => 'required_with:cuadros|integer|min:1',
            'cuadros.*.cantidad' => 'required_with:cuadros|integer|min:1',
            'cuadros.*.id_materia_prima_varillas' => 'nullable|exists:materia_prima_varillas,id',
            'cuadros.*.id_materia_prima_trupans' => 'nullable|exists:materia_prima_trupans,id',
            'cuadros.*.id_materia_prima_vidrios' => 'nullable|exists:materia_prima_vidrios,id',
            'cuadros.*.id_materia_prima_contornos' => 'nullable|exists:materia_prima_contornos,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Faltan datos o datos inválidos', 
                'errors' => $validator->errors()
            ], 400);
        }

        // Validar que al menos uno de los arrays tenga contenido
        if (empty($request->detalles) && empty($request->cuadros)) {
            return response()->json([
                'error' => 'Debe incluir al menos productos o cuadros personalizados'
            ], 400);
        }

        DB::beginTransaction();

        try {
            // 1. Crear venta base
            $venta = $this->crearVentaBase($request);
            $totalVenta = 0;

            // 2. Procesar productos regulares si existen
            if (!empty($request->detalles)) {
                $totalProductos = $this->gestionProductos->procesarProductos($venta, $request->detalles);
                $totalVenta += $totalProductos;
            }

            // 3. Procesar cuadros personalizados si existen
            if (!empty($request->cuadros)) {
                $totalCuadros = $this->gestionMarcos->procesarMarcos($venta, $request->cuadros);
                $totalVenta += $totalCuadros;
            }

            // 4. Actualizar totales de la venta
            $this->actualizarTotalesVenta($venta, $totalVenta);

            // 5. Validar y procesar pago
            $this->procesarPago($venta, $request->saldo, $totalVenta, $request->idFormaPago);

            // 6. Cargar relaciones para respuesta
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
        ]);
    }

    private function actualizarTotalesVenta($venta, $totalVenta)
    {
        $venta->update([
            'precioProducto' => $totalVenta,
            'precioTotal' => $totalVenta,
        ]);
    }

    private function procesarPago($venta, $saldo, $precioTotal, $idFormaPago)
    {
        if ($saldo > $precioTotal) {
            $exceso = $saldo - $precioTotal;
            throw new \Exception("Hay un excedente de $exceso unidades en el saldo/pago");
        }

        if ($saldo > 0) {
            Pago::create([
                'idVenta' => $venta->id,
                'idFormaPago' => $idFormaPago,
                'monto' => $saldo,
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

    /**
     * Obtiene una venta completa por ID
     */
    public function obtenerVentaCompleta($id)
    {
        try {
            $venta = Venta::with([
                'cliente', 
                'sucursal', 
                'detalleVentaProductos',
                'detalleVentaPersonalizadas.materiaPrimaVarilla',
                'detalleVentaPersonalizadas.materiaPrimaTrupan',
                'detalleVentaPersonalizadas.materiaPrimaVidrio',
                'detalleVentaPersonalizadas.materiaPrimaContorno'
            ])->find($id);

            if (!$venta) {
                return response()->json([
                    'error' => 'Venta no encontrada'
                ], 404);
            }

            return response()->json([
                'message' => 'Venta obtenida exitosamente',
                'venta' => $this->formatearRespuestaVenta($venta)
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
                $query->where('recogido', $request->recogido);
            }

            // Parámetros de paginación
            $page = (int) $request->input('page', 1);
            $perPage = (int) $request->input('per_page', 15);
            
            // Validar valores
            $perPage = max(1, min($perPage, 100));
            $page = max(1, $page);

            // Obtener total de elementos
            $totalItems = $query->count();
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
                return $this->formatearRespuestaVenta($venta);
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
                ],
                'debug_info' => [
                    'requested_per_page' => $request->get('per_page'),
                    'requested_page' => $request->get('page'),
                    'applied_per_page' => $perPage,
                    'applied_page' => $page,
                    'items_count' => $ventas->count(),
                    'total_items' => $totalItems,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error al obtener las ventas: ' . $e->getMessage()
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
            'precioProducto' => $venta->precioProducto,
            'precioTotal' => $venta->precioTotal,
            'created_at' => $venta->created_at,
            'updated_at' => $venta->updated_at,
            'cliente' => $venta->cliente,
            'sucursal' => $venta->sucursal,
            'detalle_venta_productos' => $venta->detalleVentaProductos,
            'detalle_venta_personalizadas' => $venta->detalleVentaPersonalizadas,
        ];
    }
}