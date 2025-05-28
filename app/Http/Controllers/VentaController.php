<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

//controlers

use App\Models\Producto;
use App\Models\DetalleVentaProducto;


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

    // Actualizar una venta existente
    public function update(Request $request, $id)
    {
        $venta = Venta::find($id);

        if (!$venta) {
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'precioProducto' => 'required|numeric|min:0',
            'precioPerzonalizado' => 'nullable|numeric|min:0',
            'precioTotal' => 'required|numeric|min:0',
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
    ]);

    DB::beginTransaction();

    try {
        $venta = Venta::create([
            'saldo' => $validated['saldo'],
            'idCliente' => $validated['idCliente'],
            'idSucursal' => $validated['idSucursal'],
            'recogido' => false,
            'fecha' => now(),
        ]);

        $total = 0;
        $detalles = [];

        foreach ($validated['detalles'] as $item) {
            $producto = Producto::find($item['idProducto']);

            // Validar stock suficiente
            if ($producto->stock_global_actual < $item['cantidad']) {
                DB::rollBack();
                return response()->json([
                    'error' => "Stock insuficiente para el producto ID {$producto->id}: {$producto->descripcion}"
                ], 422);
            }

            $subtotal = $item['cantidad'] * $producto->precioVenta;
            $total += $subtotal;

            $detalles[] = DetalleVentaProducto::create([
                'idVenta' => $venta->id,
                'idProducto' => $producto->id,
                'cantidad' => $item['cantidad'],
                'precio' => $producto->precioVenta,
            ]);
        }

        // Actualiza los precios finales de la venta
        $venta->update([
            'precioProducto' => $total,
            'precioTotal' => $total,
        ]);

        DB::commit();

        // Cargar relaciones para la respuesta
        $venta->load(['cliente', 'sucursal', 'detalleVentaProductos']);

        return response()->json([
            'venta' => $venta,
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

}
