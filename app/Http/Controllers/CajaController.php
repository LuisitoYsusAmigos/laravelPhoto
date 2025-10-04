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

        $pdf = Pdf::loadView('caja.cierre-caja', compact(var_name: 'caja'));
        return $pdf->stream("cierre_diario_{$fecha}.pdf");
    }
    public function cajaPorMes($fechaMes)
{
    // $fechaMes en formato YYYY-MM
    $año = \Carbon\Carbon::parse($fechaMes)->year;
    $mes = \Carbon\Carbon::parse($fechaMes)->month;

    $cajas = Caja::whereYear('fecha', $año)
                ->whereMonth('fecha', $mes)
                ->get();

    if ($cajas->isEmpty()) {
        return response()->json([
            'status' => false,
            'msg' => 'No se encontraron cierres para el mes indicado',
            'value' => null
        ], 404);
    }

    // Inicializar acumuladores
    $detalleAcumulado = [];
    $total = 0;
    $ventas = 0;

    // Contador de usuarios
    $usuariosCount = [];

    foreach ($cajas as $caja) {
        $total += $caja->total;
        $ventas += $caja->ventas;

        // Contar usuarios
        $usuariosCount[$caja->id_usuario] = ($usuariosCount[$caja->id_usuario] ?? 0) + 1;

        $detalle = json_decode($caja->detalle, true);

        if (is_array($detalle)) {
            foreach ($detalle as $idPago => $monto) {
                if (!isset($detalleAcumulado[$idPago])) {
                    $detalleAcumulado[$idPago] = 0;
                }
                $detalleAcumulado[$idPago] += (float) $monto;
            }
        }
    }

    // Obtener el id_usuario más repetido
    $idUsuario = null;
    if (!empty($usuariosCount)) {
        arsort($usuariosCount); // ordenar por mayor frecuencia
        $idUsuario = array_key_first($usuariosCount); // obtener el más repetido
    }

    // Construir la caja unificada
    $caja = [
        'detalle' => $detalleAcumulado,
        'total'   => $total,
        'ventas'  => $ventas,
        'fecha'   => $fechaMes, // devolvemos año-mes como pediste
        'id_usuario' => $idUsuario
    ];
/*
    return response()->json([
        'status' => true,
        'msg' => 'Caja unificada del mes generada correctamente',
        'value' => $caja
    ]);
*/

        $pdf = Pdf::loadView('caja.cierre-caja-mes', compact(var_name: 'caja'));
        return $pdf->stream("cierre_mensual_{$fechaMes}.pdf");
}




    public function errotest()
    {
        //dd('entro');
        return response()->json(['message' => 'llego al error'], 404);
    }
}
