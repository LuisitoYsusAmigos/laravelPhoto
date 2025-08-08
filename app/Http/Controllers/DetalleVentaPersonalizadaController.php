<?php

namespace App\Http\Controllers;

use App\Models\DetalleVentaPersonalizada;
use App\Models\MateriaPrimaVarilla;
use App\Models\StockVarilla;
use App\Models\MateriaPrimaTrupan;
use App\Models\StockTrupan;
use App\Models\MateriaPrimaVidrio;
use App\Models\StockVidrio;
use App\Models\MateriaPrimaContorno;
use App\Models\StockContorno;
use App\Models\MaterialesVentaPersonalizada;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Services\UsoVarillasCuadro;
use App\Services\UsoLaminasCuadro;

class DetalleVentaPersonalizadaController extends Controller
{
    public function index()
    {
        $detalles = DetalleVentaPersonalizada::all();

        if ($detalles->isEmpty()) {
            return response()->json(['message' => 'No hay detalles de venta personalizadas registrados'], 404);
        }

        // Agregar campos calculados
        $detallesConCalculos = $detalles->map(function ($detalle) {
            return [
                'id' => $detalle->id,
                'lado_a' => $detalle->lado_a,
                'lado_b' => $detalle->lado_b,
                'longitud_total_requerida_cm' => (2 * $detalle->lado_a) + (2 * $detalle->lado_b), // Perímetro
                'metros_cuadrados' => ($detalle->lado_a * $detalle->lado_b) / 10000, // Área en m²
                'id_materia_prima_varillas' => $detalle->id_materia_prima_varillas,
                'id_materia_prima_trupans' => $detalle->id_materia_prima_trupans,
                'id_materia_prima_vidrios' => $detalle->id_materia_prima_vidrios,
                'id_materia_prima_contornos' => $detalle->id_materia_prima_contornos,
                'id_venta' => $detalle->id_venta,
                'created_at' => $detalle->created_at,
                'updated_at' => $detalle->updated_at
            ];
        });

        return response()->json($detallesConCalculos, 200);
    }
    public function varilla()
    {
        $necesidadesCuadros = [
            ['largo' => 35, 'ancho' => 40, 'cantidad' => 1, 'nombre' => 'Cuadro']
        ];
        $retazosDisponibles = [
            [0, 120, 1],
            [1, 150, 1],
            [2, 200, 3],
            [3, 100, 1],
            [4, 180, 1],
            [5, 90, 3]
        ];
        $espesorSierra = 0.3;

        $usoVarillasCuadro = new UsoVarillasCuadro();
        $resultado = $usoVarillasCuadro->optimizarCorte($necesidadesCuadros, $retazosDisponibles, $espesorSierra);
        $jsonRespuesta = $usoVarillasCuadro->generarJson($resultado);
        return response()->json($jsonRespuesta, 200);
    }

    public function lamina()
    {
        $usoLaminasCuadro = new UsoLaminasCuadro();
        $necesidadCuadro = ['largo' => 35, 'ancho' => 40, 'cantidad' => 1, 'nombre' => 'Cuadro Pequeño'];
        // Materiales de lámina disponibles [id, largo, ancho, cantidad]
        $materialesLamina = [
            [1, 100, 100, 11],
            [2, 30, 40, 2],
            [3, 30, 70, 3],
            [4, 10, 80, 2],
        ];
        $usoLaminasCuadro = new UsoLaminasCuadro();
        $respuesta = $usoLaminasCuadro->optimizarCuadro($necesidadCuadro, $materialesLamina);
        return response()->json($respuesta, 200);
    }

    public function store(Request $request)
    {
        // Validación
        $validator = Validator::make($request->all(), [
            'lado_a' => 'required|integer|min:1',
            'lado_b' => 'required|integer|min:1',
            'cantidad' => 'required|integer|min:1',
            'id_venta' => 'required|exists:ventas,id',
            'id_materia_prima_varillas' => 'nullable|exists:materia_prima_varillas,id',
            'id_materia_prima_trupans' => 'nullable|exists:materia_prima_trupans,id',
            'id_materia_prima_vidrios' => 'nullable|exists:materia_prima_vidrios,id',
            'id_materia_prima_contornos' => 'nullable|exists:materia_prima_contornos,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Faltan datos o datos inválidos', 'errors' => $validator->errors()], 400);
        }
        $detalle = DetalleVentaPersonalizada::create($request->all());
        if (!$request->id_materia_prima_varillas == null) {
            // Validar disponibilidad de varillas
            $varilla = MateriaPrimaVarilla::find($request->id_materia_prima_varillas);
            $varillasDisponibles = StockVarilla::where('id_materia_prima_varilla', $request->id_materia_prima_varillas)
                ->where('stock', '>', 0) // Solo las que tienen stock
                ->get()
                ->map(function ($item, $index) {
                    return [$item->id, $item->largo, $item->stock];
                })
                ->values() // Resetear índices para que sean secuenciales
                ->toArray();
            $espesorSierra = 0.3;
            $usoVarillasCuadro = new UsoVarillasCuadro();
            $necesidadesCuadros = [
                ['largo' => $request->lado_a, 'ancho' => $request->lado_b, 'cantidad' => $request->cantidad, 'nombre' => 'Cuadro']
            ];
            $resultado = $usoVarillasCuadro->optimizarCorte($necesidadesCuadros, $varillasDisponibles, $espesorSierra);
            $jsonRespuesta = $usoVarillasCuadro->generarJson($resultado);
            if ($jsonRespuesta['terminado'] == true) {

                foreach ($jsonRespuesta['retazosUsados'] as $retazo) {
                    $precio = $retazo['mmUsados'];

                    // Obtener el precio de venta de la materia prima varilla
                    $precioVentaDb = DB::table('stock_varillas as sv')
                        ->join('materia_prima_varillas as mpv', 'sv.id_materia_prima_varilla', '=', 'mpv.id')
                        ->where('sv.id', $retazo['id'])
                        ->value('mpv.precioVenta');
                    $precio = $precio * $precioVentaDb;
                    MaterialesVentaPersonalizada::create([
                        'stock_contorno_id' => null,
                        'stock_trupan_id' => null,
                        'stock_vidrio_id' => null,
                        'stock_varilla_id' => $retazo['id'],
                        'cantidad' => $retazo['cantidad'],
                        'precio_unitario' => $precio,
                        'detalleVP_id' => $detalle->id
                    ]);
                }

                return response()->json($jsonRespuesta, 200);
            } else {
                return response()->json(['message' => 'Ocurrio un error con las varillas'], 404);
            }
            //return response()->json($jsonRespuesta, 200);
        }


        if (!$request->id_materia_prima_trupans == null) {
            // Para trupan
            $trupan = MateriaPrimaTrupan::find($request->id_materia_prima_trupans);
            $trupansDisponibles = StockTrupan::where('id_materia_prima_trupans', $request->id_materia_prima_trupans)
                ->where('stock', '>', 0) // Solo las que tienen stock
                ->get()
                ->map(function ($item, $index) {
                    return [$item->id, $item->alto, $item->largo, $item->stock];
                })
                ->values() // Resetear índices para que sean secuenciales
                ->toArray();


            $usoLaminasCuadro = new UsoLaminasCuadro();
            $necesidadCuadro = ['largo' => $request->lado_a, 'ancho' => $request->lado_b, 'cantidad' => $request->cantidad, 'nombre' => 'Cuadro'];
            // Materiales de lámina disponibles [id, largo, ancho, cantidad]

            $usoLaminasCuadro = new UsoLaminasCuadro();
            $respuesta = $usoLaminasCuadro->optimizarCuadro($necesidadCuadro, $trupansDisponibles);
            if ($respuesta['terminado']) {

                
            } else {
                return response()->json(['message' => 'Ocurrio un error con los trupans'], 404);
            }
            //return response()->json($respuesta, 200);
        }
        // Si no se especifica varilla, retornar respuesta simple
        if (!$request->id_materia_prima_vidrios == null) {
            // Validar disponibilidad de vidrios
            $vidrio = MateriaPrimaVidrio::find($request->id_materia_prima_vidrios);
            $vidriosDisponibles = StockVidrio::where('id_materia_prima_vidrio', $request->id_materia_prima_vidrios)
                ->where('stock', '>', 0) // Solo las que tienen stock
                ->get()
                ->map(function ($item, $index) {
                    return [$item->id, $item->alto, $item->largo, $item->stock];
                })
                ->values() // Resetear índices para que sean secuenciales
                ->toArray();
            $usoLaminasCuadro = new UsoLaminasCuadro();
            $necesidadCuadro = ['largo' => $request->lado_a, 'ancho' => $request->lado_b, 'cantidad' => $request->cantidad, 'nombre' => 'Cuadro'];
            $respuesta = $usoLaminasCuadro->optimizarCuadro($necesidadCuadro, $vidriosDisponibles);
            return response()->json($respuesta, 200);
        }
        if (!$request->id_materia_prima_contornos == null) {
            $contorno = MateriaPrimaContorno::find($request->id_materia_prima_contornos);
            $contornosDisponibles = StockContorno::where('id_materia_prima_contorno', $request->id_materia_prima_contornos)
                ->where('stock', '>', 0) // Solo las que tienen stock
                ->get()
                ->map(function ($item, $index) {
                    return [$item->id, $item->alto, $item->largo, $item->stock];
                })
                ->values() // Resetear índices para que sean secuenciales
                ->toArray();
            $usoLaminasCuadro = new UsoLaminasCuadro();
            $necesidadCuadro = ['largo' => $request->lado_a, 'ancho' => $request->lado_b, 'cantidad' => $request->cantidad, 'nombre' => 'Cuadro'];
            $respuesta = $usoLaminasCuadro->optimizarCuadro($necesidadCuadro, $contornosDisponibles);
            if (!$respuesta['terminado']) {
                return response()->json(['message' => 'Ocurrio un error con los contornos'], 404);
            }
            //return response()->json($respuesta, 200);
        }

        //return response()->json(['respuesta' => 'llego'], 200);



        //validar disponibilidad de trupans

        //validar disponibilidad de vidrios

        //validar disponibilidad de contornos

        // Crear detalle de venta personalizada

        //$detalle = DetalleVentaPersonalizada::create($request->all());

        // Cargar las relaciones para la respuesta
        $detalle->load([
            'venta',
            'materiaPrimaVarilla',
            'materiaPrimaTrupan',
            'materiaPrimaVidrio',
            'materiaPrimaContorno',

        ]);

        return response()->json(['message' => 'Detalle de venta personalizada creado', 'detalle' => $detalle], 201);
    }

    public function show($id)
    {
        $detalle = DetalleVentaPersonalizada::with([
            'venta',
            'materiaPrimaVarilla',
            'materiaPrimaTrupan',
            'materiaPrimaVidrio',
            'materiaPrimaContorno'
        ])->find($id);

        if (!$detalle) {
            return response()->json(['message' => 'No se encontró el detalle de venta personalizada'], 404);
        }

        return response()->json($detalle, 200);
    }

    public function update(Request $request, $id)
    {
        $detalle = DetalleVentaPersonalizada::find($id);

        if (!$detalle) {
            return response()->json(['message' => 'No se encontró el detalle de venta personalizada'], 404);
        }

        // Validación
        $validator = Validator::make($request->all(), [
            'lado_a' => 'nullable|integer|min:1',
            'lado_b' => 'nullable|integer|min:1',
            'id_materia_prima_varillas' => 'required|exists:materia_prima_varillas,id',
            'id_materia_prima_trupans' => 'required|exists:materia_prima_trupans,id',
            'id_materia_prima_vidrios' => 'required|exists:materia_prima_vidrios,id',
            'id_materia_prima_contornos' => 'required|exists:materia_prima_contornos,id',
            'id_venta' => 'required|exists:ventas,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Faltan datos o datos inválidos', 'errors' => $validator->errors()], 400);
        }

        // Actualizar detalle
        $detalle->update($request->all());

        // Cargar las relaciones para la respuesta
        $detalle->load([
            'venta',
            'materiaPrimaVarilla',
            'materiaPrimaTrupan',
            'materiaPrimaVidrio',
            'materiaPrimaContorno'
        ]);

        return response()->json(['message' => 'Detalle de venta personalizada actualizado', 'detalle' => $detalle], 200);
    }

    public function destroy($id)
    {
        $detalle = DetalleVentaPersonalizada::find($id);

        if (!$detalle) {
            return response()->json(['message' => 'No se encontró el detalle de venta personalizada'], 404);
        }

        $detalle->delete();
        return response()->json(['message' => 'Detalle de venta personalizada eliminado'], 200);
    }

    // Método adicional para obtener detalles por venta
    public function getByVenta($id_venta)
    {
        $detalles = DetalleVentaPersonalizada::with([
            'materiaPrimaVarilla',
            'materiaPrimaTrupan',
            'materiaPrimaVidrio',
            'materiaPrimaContorno'
        ])->where('id_venta', $id_venta)->get();

        if ($detalles->isEmpty()) {
            return response()->json(['message' => 'No hay detalles para esta venta'], 404);
        }

        return response()->json($detalles, 200);
    }
}
