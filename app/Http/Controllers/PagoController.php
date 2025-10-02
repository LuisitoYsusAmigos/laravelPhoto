<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pago;
use App\Models\Venta;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\DB;
class PagoController extends Controller
{
    // Listar todos los pagos
    public function index()
    {
        $pagos = Pago::all();
        return response()->json($pagos);
    }

    // Crear un nuevo pago
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'idVenta' => 'required|exists:ventas,id',
            'idFormaPago' => 'required|exists:forma_de_pagos,id',
            'monto' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $data = $request->all();

        $venta = Venta::find($data['idVenta']);

        $saldoActual = $venta->saldo;
        $nuevoMonto = $data['monto'];
        $nuevoTotal = $saldoActual + $nuevoMonto;

        if ($nuevoTotal > $venta->precioTotal) {
            $exceso = $nuevoTotal - $venta->precioTotal;
            return response()->json([
                'error' => "Hay un excedente de $exceso unidades."
            ], 400);
        }

        if (!isset($data['fecha'])) {
            $data['fecha'] = Carbon::now()->toDateString(); // yyyy-mm-dd
        }

        $pago = Pago::create($data);
        $this->actualizarSaldoVenta($pago->idVenta);

        return response()->json($pago, 201);
    }

    // Mostrar un pago por ID
    public function show($id)
    {
        $pago = Pago::with(['venta', 'formaPago'])->find($id);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        return response()->json($pago);
    }

    // Actualizar un pago
    public function update(Request $request, $id)
    {
        $pago = Pago::find($id);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'monto' => 'required|integer|min:1'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 400);
        }

        $nuevoMonto = $request->monto;
        $venta = Venta::find($pago->idVenta);

        // saldo - monto actual del pago + nuevoMonto
        $saldoProyectado = $venta->saldo - $pago->monto + $nuevoMonto;

        if ($saldoProyectado > $venta->precioTotal) {
            $exceso = $saldoProyectado - $venta->precioTotal;
            return response()->json([
                'error' => "Hay un excedente de $exceso unidades."
            ], 400);
        }

        $pago->update($request->all());
        $this->actualizarSaldoVenta($pago->idVenta);

        return response()->json($pago);
    }

    // Eliminar un pago
    public function destroy($id)
    {
        $pago = Pago::find($id);

        if (!$pago) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        $idVenta = $pago->idVenta;
        $pago->delete();
        $this->actualizarSaldoVenta($idVenta);

        return response()->json(['message' => 'Pago eliminado correctamente']);
    }

    // Función auxiliar para actualizar el saldo en la venta
    private function actualizarSaldoVenta($idVenta)
    {
        $nuevoSaldo = Pago::where('idVenta', $idVenta)->sum('monto');
        Venta::where('id', $idVenta)->update(['saldo' => $nuevoSaldo]);
    }


    // Completar el pago restante de una venta
    // Completar el pago restante de una venta
public function completarPago(Request $request)
{
    $validator = Validator::make($request->all(), [
        'idVenta' => 'required|exists:ventas,id',
        'idFormaPago' => 'required|exists:forma_de_pagos,id',
    ]);

    if ($validator->fails()) {
        return response()->json($validator->errors(), 400);
    }

    $venta = Venta::find($request->idVenta);
    
    // Validar que el precio total y saldo sean diferentes (venta incompleta)
    if ($venta->precioTotal == $venta->saldo) {
        //echo"venta completa";
        DB::update('UPDATE ventas SET recogido = 1 WHERE id = ?', [$venta->id]);
        if ($venta->precioTotal == $venta->saldo) {
    $ventaController = new VentaController();
    return response()->json([
            'error' => 'La venta ya está completamente pagada'
        ], 400);
        }
    }
    
    // Calcular el monto faltante
    $montoFaltante = $venta->precioTotal - $venta->saldo;
    
    // Validación adicional por si acaso el saldo fuera mayor al precio total
    if ($montoFaltante <= 0) {
        return response()->json([
            'error' => 'Error en los datos de la venta: el saldo es mayor o igual al precio total'
        ], 400);
    }
    
    // Crear el pago con el monto faltante
    $pago = Pago::create([
        'idVenta' => $request->idVenta,
        'idFormaPago' => $request->idFormaPago,
        'monto' => $montoFaltante,
        'fecha' => Carbon::now()->toDateString()
    ]);
    
    // Actualizar el saldo de la venta
    $this->actualizarSaldoVenta($request->idVenta);

    $venta->update([
    'entregado' => true
    ]);
    DB::update('UPDATE ventas SET recogido = 1 WHERE id = ?', [$venta->id]);
    
    
    return response()->json([
        'message' => 'Pago completado exitosamente',
        'pago' => $pago,
        'monto_pagado' => $montoFaltante,
        'venta_completada' => true
    ], 201);
}
}
