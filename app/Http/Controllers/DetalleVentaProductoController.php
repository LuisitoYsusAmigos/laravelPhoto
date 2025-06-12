<?php

namespace App\Http\Controllers;

use App\Models\DetalleVentaProducto;
use Illuminate\Http\Request;

class DetalleVentaProductoController extends Controller
{
    // Listado paginado
    public function index(Request $request)
    {
        $perPage = $request->input('perPage', 10);
        $detalles = DetalleVentaProducto::with(['producto', 'stockProducto', 'venta'])->paginate($perPage);
        return response()->json($detalles);
    }

    // Ver uno
    public function show($id)
    {
        $detalle = DetalleVentaProducto::with(['producto', 'stockProducto', 'venta'])->find($id);

        if (!$detalle) {
            return response()->json(['message' => 'Detalle no encontrado'], 404);
        }

        return response()->json($detalle);
    }

    // Crear nuevo (registro manual, normalmente se hace desde VentaController)
    public function store(Request $request)
    {
        $validated = $request->validate([
            'cantidad' => 'required|integer|min:1',
            'precio' => 'required|numeric|min:0',
            'idVenta' => 'required|exists:ventas,id',
            'idProducto' => 'required|exists:productos,id',
            'id_stock_producto' => 'required|exists:stock_productos,id',
        ]);

        $detalle = DetalleVentaProducto::create($validated);
        return response()->json($detalle, 201);
    }

    // Actualizar
    public function update(Request $request, $id)
    {
        $detalle = DetalleVentaProducto::find($id);

        if (!$detalle) {
            return response()->json(['message' => 'Detalle no encontrado'], 404);
        }

        $validated = $request->validate([
            'cantidad' => 'required|integer|min:1',
            'precio' => 'required|numeric|min:0',
            'idVenta' => 'required|exists:ventas,id',
            'idProducto' => 'required|exists:productos,id',
            'id_stock_producto' => 'required|exists:stock_productos,id',
        ]);

        $detalle->update($validated);
        return response()->json($detalle);
    }

    // Eliminar
    public function destroy($id)
    {
        $detalle = DetalleVentaProducto::find($id);

        if (!$detalle) {
            return response()->json(['message' => 'Detalle no encontrado'], 404);
        }

        $detalle->delete();
        return response()->json(['message' => 'Detalle eliminado correctamente']);
    }
}
