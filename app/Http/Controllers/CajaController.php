<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caja;
use Illuminate\Support\Facades\Validator;

class CajaController extends Controller
{
    // Listar todas las cajas
public function index()
{
    $cajas = Caja::all();

    // Procesar cada caja
    $cajas->transform(function ($caja) {
        $caja->detalle = json_decode($caja->detalle);
        unset($caja->usuario); // por si acaso Laravel intenta incluirla
        return $caja;
    });

    return response()->json($cajas);
}



    // Crear una nueva caja
public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'id_usuario' => 'required|exists:users,id',
        'fecha' => 'nullable|date', // fecha opcional
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    // Usar la fecha proporcionada o la del sistema
    $fecha = $request->filled('fecha') ? date('Y-m-d', strtotime($request->fecha)) : now()->toDateString();

    // Validar que no exista ya una caja con esa fecha
    $cajaExistente = Caja::where('fecha', $fecha)->first();
    if ($cajaExistente) {
        return response()->json(['error' => 'Ya existe una caja registrada para la fecha ' . $fecha], 409);
    }

    // Calcular cantidad de ventas en esa fecha
    $ventasDelDia = \App\Models\Venta::whereDate('fecha', $fecha)->count();

    // Sumar total de pagos en esa fecha
    $totalPagos = \App\Models\Pago::whereDate('fecha', $fecha)->sum('monto');

    // Agrupar pagos por forma de pago
    $pagosPorForma = \App\Models\Pago::whereDate('fecha', $fecha)
        ->selectRaw('idFormaPago, SUM(monto) as total')
        ->groupBy('idFormaPago')
        ->get();

    // Crear el detalle como JSON string
    $detalleArray = [];
    foreach ($pagosPorForma as $pago) {
        $detalleArray[$pago->idFormaPago] = $pago->total;
    }

    $detalleJson = json_encode($detalleArray, JSON_PRETTY_PRINT);

    // Crear la caja
    $caja = Caja::create([
        'detalle' => $detalleJson,
        'total' => $totalPagos,
        'ventas' => $ventasDelDia,
        'fecha' => $fecha,
        'id_usuario' => $request->id_usuario,
    ]);

    return response()->json($caja, 201);
}



    // Mostrar una caja por su ID
public function show($id)
{
    $caja = Caja::find($id);

    if (!$caja) {
        return response()->json(['message' => 'Caja no encontrada'], 404);
    }

    $caja->detalle = json_decode($caja->detalle);
    unset($caja->usuario);

    return response()->json($caja);
}



    // Actualizar una caja
    public function update(Request $request, $id)
    {
        $caja = Caja::find($id);

        if (!$caja) {
            return response()->json(['message' => 'Caja no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'detalle' => 'required|string',
            'total' => 'required|integer|min:0',
            'ventas' => 'required|integer|min:0',
            'fecha' => 'required|date',
            'id_usuario' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $caja->update($request->all());

        return response()->json($caja);
    }

    // Eliminar una caja
    public function destroy($id)
    {
        $caja = Caja::find($id);

        if (!$caja) {
            return response()->json(['message' => 'Caja no encontrada'], 404);
        }

        $caja->delete();

        return response()->json(['message' => 'Caja eliminada correctamente']);
    }

    // Obtener caja por fecha exacta
public function obtenerPorFecha($fecha)
{
    $cajas = Caja::with('usuario')->where('fecha', $fecha)->get();

    if ($cajas->isEmpty()) {
        return response()->json(['message' => 'No se encontró ninguna caja con esa fecha'], 404);
    }

    return response()->json($cajas);
}

}
