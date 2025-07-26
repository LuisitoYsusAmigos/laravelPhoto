<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
//controlers

use App\Models\Producto;
use App\Models\DetalleVentaProducto;
use App\Http\Controllers\PagoController;
use App\Models\Pago;


class VentaController extends Controller
{
    // Listar todas las ventas
    public function index()
    {
        return response()->json(Venta::all());
    }

    // Listar ventas con paginación
    public function indexPaginado(Request $request)
    {
        $page = $request->input('page', 1);
        $perPage = $request->input('perPage', 10);

        $totalItems = Venta::count();
        $totalPages = ceil($totalItems / $perPage);

        $ventas = Venta::skip(($page - 1) * $perPage)
                       ->take($perPage)
                       ->get();

        return response()->json([
            'currentPage' => (int)$page,
            'perPage' => (int)$perPage,
            'totalItems' => $totalItems,
            'totalPages' => $totalPages,
            'data' => $ventas
        ]);
    }

    // Buscar ventas por campos
    public function search(Request $request)
    {
        $searchTerm = $request->input('search', '');

        if (empty($searchTerm)) {
            return response()->json(Venta::all());
        }

        $ventas = Venta::where('precioProducto', 'LIKE', "%{$searchTerm}%")
                       ->orWhere('precioTotal', 'LIKE', "%{$searchTerm}%")
                       ->orWhere('saldo', 'LIKE', "%{$searchTerm}%")
                       ->orWhere('idCliente', 'LIKE', "%{$searchTerm}%")
                       ->orWhere('idSucursal', 'LIKE', "%{$searchTerm}%")
                       ->get();

        if ($ventas->isEmpty()) {
            return response()->json([
                'message' => 'No se encontraron coincidencias para: ' . $searchTerm,
                'data' => []
            ]);
        }

        return response()->json($ventas);
    }

    // Registrar nueva venta
    public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        //'precioProducto' => 'required|numeric|min:0',
        //'precioPerzonalizado' => 'nullable|numeric|min:0',
        //'precioTotal' => 'required|numeric|min:0',
        'saldo' => 'nullable|numeric|min:0',
        'recogido' => 'nullable|boolean',
        'idCliente' => 'required|exists:clientes,id',
        'idSucursal' => 'required|exists:sucursal,id'
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $data = $request->all();
    $data['fecha'] = now(); // Asignamos la fecha actual del servidor
    $data['recogido'] = $data['recogido'] ?? false; // Aseguramos que 'recogido' tenga un valor booleano
    $data['precioPerzonalizado'] = $data['precioPerzonalizado'] ?? 0; // Aseguramos que 'precioPerzonalizado' tenga un valor numérico
    $data['precioProducto'] = $data['precioProducto'] ?? 0; // Aseguramos que 'precioProducto' tenga un valor numérico
    $data['precioTotal'] = $data['precioTotal'] ?? 0; // Aseguramos que 'precioTotal' tenga un valor numérico
    // Si 'saldo' es null o no está, establecerlo como 0
    if (!isset($data['saldo']) || $data['saldo'] === null) {
        $data['saldo'] = 0;
    }
    $venta = Venta::create($data);
    return response()->json($venta, 201);
}


    // Ver una venta por ID
    public function show($id)
    {
        $venta = Venta::find($id);

        if (!$venta) {
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }

        return response()->json($venta);
    }
    // Mostrar los detalles de venta-producto de una venta específica
public function showVentaDetalleProducto($idVenta)
{
    $detalles = DetalleVentaProducto::with('stockProducto')
        ->where('idVenta', $idVenta)
        ->get()
        ->map(function ($detalle) {
            return [
                'id' => $detalle->id,
                'idVenta' => $detalle->idVenta,
                'idProducto' => $detalle->idProducto, // solo el ID, no la relación completa
                'id_stock_producto' => $detalle->id_stock_producto,
                'cantidad' => $detalle->cantidad,
                'precio' => $detalle->precio,
                'created_at' => $detalle->created_at,
                'updated_at' => $detalle->updated_at,
                'stock_producto' => $detalle->stockProducto, // mantenemos toda la info del lote
            ];
        });

    return response()->json($detalles);
}



    // Actualizar una venta existente
    public function update(Request $request, $id)
    {
        $venta = Venta::find($id);

        if (!$venta) {
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            //'precioProducto' => 'required|numeric|min:0',
            //'precioPerzonalizado' => 'nullable|numeric|min:0',
            //'precioTotal' => 'required|numeric|min:0',
            'saldo' => 'required|numeric|min:0',
            'recogido' => 'required|boolean',
            'fecha' => 'required|date',
            'idCliente' => 'required|exists:clientes,id',
            'idSucursal' => 'required|exists:sucursal,id'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $venta->update($request->all());
        return response()->json($venta);
    }

    // Eliminar una venta
    public function destroy($id)
    {
        $venta = Venta::find($id);

        if (!$venta) {
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }

        $venta->delete();
        return response()->json(['message' => 'Venta eliminada correctamente']);
    }

    // Total de ventas
    public function totalVentas()
    {
        $resultado = DB::select("SELECT COUNT(*) AS total_ventas FROM ventas");

        return response()->json([
            'total_ventas' => $resultado[0]->total_ventas
        ]);
    }


public function storeConDetalle(Request $request)
{
    $validated = $request->validate([
        'saldo' => 'required|numeric|min:0',
        'idCliente' => 'required|exists:clientes,id',
        'idSucursal' => 'required|exists:sucursal,id',
        'detalles' => 'required|array|min:1',
        'detalles.*.idProducto' => 'required|exists:productos,id',
        'detalles.*.cantidad' => 'required|integer|min:1',
        'idFormaPago' => 'required|exists:forma_de_pagos,id', // <- nuevo campo obligatorio
    ]);

    DB::beginTransaction();

    try {
        // Crear venta base
        $venta = Venta::create([
            'saldo' => $validated['saldo'],
            'idCliente' => $validated['idCliente'],
            'idSucursal' => $validated['idSucursal'],
            'recogido' => false,
            'fecha' => now(),
        ]);

        $totalVenta = 0;

        foreach ($validated['detalles'] as $item) {
            $producto = Producto::find($item['idProducto']);

            if (!$producto) {
                DB::rollBack();
                return response()->json([
                    'error' => "Producto ID {$item['idProducto']} no encontrado"
                ], 404);
            }

            if ($producto->stock_global_actual < $item['cantidad']) {
                DB::rollBack();
                return response()->json([
                    'error' => "Stock insuficiente para el producto ID {$producto->id}: {$producto->descripcion}"
                ], 422);
            }

            $cantidadRestante = $item['cantidad'];
            $precioUnitario = $producto->precioVenta;
            $totalProducto = $precioUnitario * $cantidadRestante;
            $totalVenta += $totalProducto;

            $lotes = DB::table('stock_productos')
                ->where('id_producto', $producto->id)
                ->where('contable', 1)
                ->where('stock', '>', 0)
                ->orderBy('id')
                ->get();

            foreach ($lotes as $lote) {
                if ($cantidadRestante <= 0) break;

                $usar = min($cantidadRestante, $lote->stock);

                DB::table('stock_productos')
                    ->where('id', $lote->id)
                    ->decrement('stock', $usar);

                DetalleVentaProducto::create([
                    'idVenta' => $venta->id,
                    'idProducto' => $producto->id,
                    'id_stock_producto' => $lote->id,
                    'cantidad' => $usar,
                    'precio' => $lote->precio,
                ]);

                $cantidadRestante -= $usar;
            }

            if ($cantidadRestante > 0) {
                DB::rollBack();
                return response()->json([
                    'error' => "No se pudo descontar todo el stock necesario para el producto ID {$producto->id}"
                ], 500);
            }
        }

        // Guardar total
        $venta->update([
            'precioProducto' => $totalVenta,
            'precioTotal' => $totalVenta,
        ]);

        // Validar e insertar pago asociado
        $saldo = $validated['saldo'];
        $precioTotal = $totalVenta;
        if ($saldo > $precioTotal) {
            DB::rollBack();
            $exceso = $saldo - $precioTotal;
            return response()->json([
                'error' => "Hay un excedente de $exceso unidades en el saldo/pago"
            ], 400);
        }

        // Insertar pago si el saldo > 0
        if ($saldo > 0) {
            Pago::create([
                'idVenta' => $venta->id,
                'idFormaPago' => $validated['idFormaPago'],
                'monto' => $saldo,
                'fecha' => now()->toDateString(),
            ]);
        }

        // Cargar relaciones
        $venta->load(['cliente', 'sucursal', 'detalleVentaProductos']);

        foreach ($venta->detalleVentaProductos as $detalle) {
            $producto = Producto::find($detalle->idProducto);
            $detalle->precioDetalle = $detalle->cantidad * $producto->precioVenta;
        }

        DB::commit();

        return response()->json([
            'venta' => [
                'id' => $venta->id,
                'saldo' => $venta->saldo,
                'idCliente' => $venta->idCliente,
                'idSucursal' => $venta->idSucursal,
                'recogido' => $venta->recogido,
                'fecha' => $venta->fecha,
                'updated_at' => $venta->updated_at,
                'created_at' => $venta->created_at,
                'precioProducto' => $venta->precioProducto,
                'precioTotal' => $venta->precioTotal,
                'cliente' => $venta->cliente,
                'sucursal' => $venta->sucursal,
                'detalle_venta_productos' => $venta->detalleVentaProductos,
            ],
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}


public function getVentaCompleta($id)
{
    try {
        // Carga las relaciones necesarias, incluyendo producto dentro de cada detalle
        $venta = Venta::with([
            'cliente',
            'sucursal',
            'detalleVentaProductos.producto'
        ])->find($id);

        if (!$venta) {
            return response()->json(['error' => 'Venta no encontrada'], 404);
        }

        // Añadir precioDetalle y nombreProducto a cada detalle, y eliminar el objeto producto
        foreach ($venta->detalleVentaProductos as $detalle) {
            $producto = $detalle->producto;
            $detalle->precioDetalle = $detalle->cantidad * $producto->precioVenta;
            $detalle->nombreProducto = $producto->descripcion;

            // Eliminar la relación completa para no devolverla en el JSON
            unset($detalle->producto);
        }

        // Estructura de respuesta
        return response()->json([
            'venta' => [
                'id' => $venta->id,
                'saldo' => $venta->saldo,
                'idCliente' => $venta->idCliente,
                'idSucursal' => $venta->idSucursal,
                'recogido' => $venta->recogido,
                'fecha' => $venta->fecha,
                'updated_at' => $venta->updated_at,
                'created_at' => $venta->created_at,
                'precioProducto' => $venta->precioProducto,
                'precioTotal' => $venta->precioTotal,
                'cliente' => $venta->cliente,
                'sucursal' => $venta->sucursal,
                'detalle_venta_productos' => $venta->detalleVentaProductos,
            ],
        ], 200);

    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}




public function completarVenta($id)
{
    $venta = Venta::find($id);

    if (!$venta) {
        return response()->json(['error' => 'Venta no encontrada'], 404);
    }

    $venta->saldo = $venta->precioTotal;
    $venta->recogido = true;
    $venta->save();

    return response()->json([
        'message' => 'Venta completada exitosamente',
        'venta' => $venta
    ], 200);
}

public function resumenDashboard(Request $request)
{
    // Obtener fecha desde query param, con fallback a hoy
    $fechaParam = $request->query('fecha');
    
    try {
        $fecha = $fechaParam 
            ? \Carbon\Carbon::createFromFormat('d-m-y', $fechaParam)->format('Y-m-d')
            : now()->format('Y-m-d');
    } catch (\Exception $e) {
        return response()->json(['error' => 'Formato de fecha inválido. Usa d-m-y, por ejemplo: 01-12-25'], 400);
    }

    // Obtener ventas del día especificado
    $ventas = Venta::whereDate('fecha', $fecha)->get();

    $totalVentas = $ventas->sum('precioTotal');
    $ventasCompletadas = $ventas->where('recogido', true)->count();

    $totalEfectivo = $ventas->sum('saldo');

    $ventasPendientes = $ventas->where('recogido', false)->count();

    return response()->json([
        'fecha_consultada' => $fecha,
        'ventas_dia' => [
            'monto' => $totalVentas,
            'cantidad' => $ventasCompletadas,
        ],
        'efectivo' => $totalEfectivo,
        'pendientes' => $ventasPendientes,
    ]);
}

public function ventasPorFechaDetallado(Request $request)
{
    $fechaParam = $request->query('fecha');

    try {
        $fecha = $fechaParam 
            ? \Carbon\Carbon::createFromFormat('d-m-y', $fechaParam)->format('Y-m-d')
            : now()->format('Y-m-d');
    } catch (\Exception $e) {
        return response()->json(['error' => 'Formato de fecha inválido. Usa d-m-y, por ejemplo: 06-12-01'], 400);
    }

    $ventas = \App\Models\Venta::with('cliente')
        ->whereDate('fecha', $fecha)
        ->orderBy('fecha', 'asc')
        ->get();

    $resultado = $ventas->map(function ($venta) {
        $tipoProducto = [];

        if ($venta->precioProducto > 0) {
            $tipoProducto[] = 'Producto';
        }

        if ($venta->precioPerzonalizado > 0) {
            $tipoProducto[] = 'Cuadro personalizado';
        }

        return [
            'id' => $venta->id,
            'hora' => \Carbon\Carbon::parse($venta->fecha)->format('H:i'),
            'cliente' => $venta->cliente ? $venta->cliente->nombre . ' ' . $venta->cliente->apellido : 'Cliente desconocido',
            'productos' => implode(', ', $tipoProducto),
            'total' => $venta->precioTotal,
            'estado' => $venta->recogido ? 'Completado' : 'Pendiente',
        ];
    });

    return response()->json([
        'fecha_consultada' => $fecha,
        'ventas' => $resultado
    ]);
}


    public function resumenDelDia(Request $request)
    {
        $fechaParam = $request->query('fecha');

        try {
            $fecha = $fechaParam
                ? Carbon::createFromFormat('d-m-y', $fechaParam)->format('Y-m-d')
                : now()->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Formato de fecha inválido. Usa d-m-y'], 400);
        }

        $ventas = Venta::whereDate('fecha', $fecha)->get();

        $totalVentas = $ventas->sum('precioTotal');
        $numeroVentas = $ventas->count();
        $totalEfectivo = $ventas->sum('saldo');

        // Aquí podrías filtrar por tipo de pago si tuvieras un campo, por ahora simulado
        $totalTarjeta = 0.00;
        $totalTransferencia = 0.00;

        return response()->json([
            'fecha_consultada' => $fecha,
            'total_ventas' => round($totalVentas, 2),
            'numero_ventas' => $numeroVentas,
            'efectivo' => round($totalEfectivo, 2),
            'tarjeta' => $totalTarjeta,
            'transferencia' => $totalTransferencia
        ]);
    }










}
