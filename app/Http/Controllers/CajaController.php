<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Caja;
use App\Models\FormaDePago;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;


class CajaController extends Controller
{
    // Listar todas las cajas
public function index()
{
    $cajas = Caja::all();

    // Procesar cada caja
    $cajas->transform(function ($caja) {
        if (is_string($caja->detalle)) {
            if (is_string($caja->detalle)) {
                $caja->detalle = json_decode($caja->detalle);
            }
        }
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

    $cajas->transform(function ($caja) {
        // Decodificar el detalle JSON
        $detalle = json_decode($caja->detalle, true);
        
        // Obtener los IDs de formas de pago del detalle
        $formasPagoIds = array_keys($detalle);
        
        // Consultar los nombres de las formas de pago
        $formasPago = FormaDePago::whereIn('id', $formasPagoIds)->get()->keyBy('id');
        
        // Transformar el detalle para incluir el nombre
        $detalleConNombres = [];
        foreach ($detalle as $formaPagoId => $monto) {
            $formaPago = $formasPago->get($formaPagoId);
            $detalleConNombres[] = [
                'forma_pago_id' => $formaPagoId,
                'nombre' => $formaPago ? $formaPago->nombre : 'Desconocido',
                'monto' => $monto
            ];
        }
        
        $caja->detalle = $detalleConNombres;
        unset($caja->usuario);
        
        return $caja;
    });

    return response()->json($cajas);
}


public function cajaPorMes($mes)
{
    // Validar formato de mes: YYYY-MM
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $mes)) {
        return response()->json(['error' => 'Formato de mes inválido. Usa YYYY-MM.'], 400);
    }

    // Obtener todas las cajas del mes
    $cajas = Caja::where('fecha', 'like', "$mes%")->get();

    if ($cajas->isEmpty()) {
        return response()->json(['message' => 'No se encontraron cajas para ese mes'], 404);
    }

    $sumaTotal = 0;
    $sumaVentas = 0;
    $detalleAcumulado = [];

    foreach ($cajas as $caja) {
        $sumaTotal += $caja->total;
        $sumaVentas += $caja->ventas;

        $detalle = $caja->detalle;
        if (is_string($detalle)) {
            $detalle = json_decode($detalle, true);
        }

        foreach ($detalle as $idFormaPago => $monto) {
            if (!isset($detalleAcumulado[$idFormaPago])) {
                $detalleAcumulado[$idFormaPago] = 0;
            }
            $detalleAcumulado[$idFormaPago] += $monto;
        }
    }

    return response()->json([
        'mes' => $mes,
        'total_cajas' => $cajas->count(),
        'total_ingresos' => $sumaTotal,
        'total_ventas' => $sumaVentas,
        'detalle_acumulado' => $detalleAcumulado,
    ]);
}




public function htmlPorDia($fecha)
{
    // Suponiendo que trabajas con Eloquent
    $caja = Caja::where('fecha', $fecha)->first();

    if (!$caja) {
        abort(404, 'No se encontró un cierre para la fecha indicada');
    }

    return view('caja/cierre-caja', compact('caja'));
}

public function htmlPorMes($mes)
{
    // $mes debe venir en formato YYYY-MM, ejemplo: 2025-07
    $cajas = Caja::whereYear('fecha', Carbon::parse($mes)->year)
                ->whereMonth('fecha', Carbon::parse($mes)->month)
                ->orderBy('fecha', 'desc')
                ->get();

    if ($cajas->isEmpty()) {
        abort(404, 'No se encontraron cierres para el mes indicado');
    }

    return view('caja/cierre-caja-lista', compact('cajas'));
}


    public function pdfPorDia($fecha)
    {
        $caja = Caja::where('fecha', $fecha)->first();

        if (!$caja) {
            abort(404, 'No se encontró un cierre para la fecha indicada');
        }

        $pdf = Pdf::loadView('caja.cierre-caja', compact('caja'));
        return $pdf->stream("cierre_diario_{$fecha}.pdf");
    }

    public function pdfPorMes($mes)
    {
        // $mes en formato YYYY-MM
        $cajas = Caja::whereYear('fecha', Carbon::parse($mes)->year)
                    ->whereMonth('fecha', Carbon::parse($mes)->month)
                    ->orderBy('fecha', 'desc')
                    ->get();

        if ($cajas->isEmpty()) {
            abort(404, 'No se encontraron cierres para el mes indicado');
        }

        // Armar estructura acumulada para el mes
        $detalleAcumulado = [];

        foreach ($cajas as $caja) {
            foreach ($caja->detalle as $forma => $monto) {
                if (!isset($detalleAcumulado[$forma])) {
                    $detalleAcumulado[$forma] = 0;
                }
                $detalleAcumulado[$forma] += $monto;
            }
        }

        $caja = [
            'mes' => $mes,
            'total_cajas' => $cajas->count(),
            'total_ingresos' => $cajas->sum('total'),
            'total_ventas' => $cajas->sum('ventas'),
            'detalle_acumulado' => $detalleAcumulado,
        ];

        $pdf = Pdf::loadView('caja.cierre-caja-lista', compact('caja'));
        return $pdf->stream("cierre_mensual_{$mes}.pdf");
    }




}
