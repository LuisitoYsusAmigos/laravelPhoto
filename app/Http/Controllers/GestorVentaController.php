<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\DetalleVentaProducto;
use App\Models\DetalleVentaPersonalizada;
use App\Models\MaterialesVentaPersonalizada;
use App\Models\Pago;
use App\Models\MateriaPrimaVarilla;
use App\Models\MateriaPrimaTrupan;
use App\Models\MateriaPrimaVidrio;
use App\Models\MateriaPrimaContorno;
use App\Models\StockVarilla;
use App\Models\StockTrupan;
use App\Models\StockVidrio;
use App\Models\StockContorno;
use App\Services\UsoVarillasCuadro;
use App\Services\UsoLaminasCuadro;

class GestorVentaController extends Controller
{
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
            $venta = Venta::create([
                'saldo' => $request->saldo,
                'idCliente' => $request->idCliente,
                'idSucursal' => $request->idSucursal,
                'idUsuario' => $request->idUsuario,
                'recogido' => false,
                'fecha' => now(),
            ]);

            $totalVenta = 0;

            // 2. Procesar productos regulares si existen
            if (!empty($request->detalles)) {
                $totalProductos = $this->procesarProductosRegulares($venta, $request->detalles);
                $totalVenta += $totalProductos;
            }

            // 3. Procesar cuadros personalizados si existen
            if (!empty($request->cuadros)) {
                $totalCuadros = $this->procesarCuadrosPersonalizados($venta, $request->cuadros);
                $totalVenta += $totalCuadros;
            }

            // 4. Actualizar totales de la venta
            $venta->update([
                'precioProducto' => $totalVenta,
                'precioTotal' => $totalVenta,
            ]);

            // 5. Validar y procesar pago
            $this->procesarPago($venta, $request->saldo, $totalVenta, $request->idFormaPago);

            // 6. Cargar relaciones para respuesta
            $venta->load([
                'cliente', 
                'sucursal', 
                'detalleVentaProductos',
                'detalleVentaPersonalizadas.materiaPrimaVarilla',
                'detalleVentaPersonalizadas.materiaPrimaTrupan',
                'detalleVentaPersonalizadas.materiaPrimaVidrio',
                'detalleVentaPersonalizadas.materiaPrimaContorno'
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Venta completa creada exitosamente',
                'venta' => [
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
                ],
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function procesarProductosRegulares($venta, $detalles)
    {
        $totalProductos = 0;

        foreach ($detalles as $item) {
            $producto = Producto::find($item['idProducto']);

            if (!$producto) {
                throw new \Exception("Producto ID {$item['idProducto']} no encontrado");
            }

            if ($producto->stock_global_actual < $item['cantidad']) {
                throw new \Exception("Stock insuficiente para el producto ID {$producto->id}: {$producto->descripcion}");
            }

            $cantidadRestante = $item['cantidad'];
            $precioUnitario = $producto->precioVenta;
            $totalProducto = $precioUnitario * $cantidadRestante;
            $totalProductos += $totalProducto;

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
                throw new \Exception("No se pudo descontar todo el stock necesario para el producto ID {$producto->id}");
            }
        }

        return $totalProductos;
    }

    private function procesarCuadrosPersonalizados($venta, $cuadros)
    {
        $totalCuadros = 0;

        foreach ($cuadros as $cuadro) {
            // Crear detalle de venta personalizada
            $detalle = DetalleVentaPersonalizada::create([
                'lado_a' => $cuadro['lado_a'],
                'lado_b' => $cuadro['lado_b'],
                'id_venta' => $venta->id,
                'id_materia_prima_varillas' => $cuadro['id_materia_prima_varillas'] ?? null,
                'id_materia_prima_trupans' => $cuadro['id_materia_prima_trupans'] ?? null,
                'id_materia_prima_vidrios' => $cuadro['id_materia_prima_vidrios'] ?? null,
                'id_materia_prima_contornos' => $cuadro['id_materia_prima_contornos'] ?? null,
            ]);

            $totalCuadro = 0;

            // Procesar varillas si están especificadas
            if (!empty($cuadro['id_materia_prima_varillas'])) {
                $totalCuadro += $this->procesarVarillas($detalle, $cuadro);
            }

            // Procesar trupans si están especificados
            if (!empty($cuadro['id_materia_prima_trupans'])) {
                $totalCuadro += $this->procesarTrupans($detalle, $cuadro);
            }

            // Procesar vidrios si están especificados
            if (!empty($cuadro['id_materia_prima_vidrios'])) {
                $totalCuadro += $this->procesarVidrios($detalle, $cuadro);
            }

            // Procesar contornos si están especificados
            if (!empty($cuadro['id_materia_prima_contornos'])) {
                $totalCuadro += $this->procesarContornos($detalle, $cuadro);
            }

            $totalCuadros += $totalCuadro;
        }

        return $totalCuadros;
    }

    private function procesarVarillas($detalle, $cuadro)
    {
        $varillasDisponibles = StockVarilla::where('id_materia_prima_varilla', $cuadro['id_materia_prima_varillas'])
            ->where('stock', '>', 0)
            ->get()
            ->map(function ($item) {
                return [$item->id, $item->largo, $item->stock];
            })
            ->values()
            ->toArray();

        $espesorSierra = 0.3;
        $usoVarillasCuadro = new UsoVarillasCuadro();
        $necesidadesCuadros = [
            [
                'largo' => $cuadro['lado_a'], 
                'ancho' => $cuadro['lado_b'], 
                'cantidad' => $cuadro['cantidad'], // Usar cantidad del JSON
                'nombre' => 'Cuadro'
            ]
        ];

        $resultado = $usoVarillasCuadro->optimizarCorte($necesidadesCuadros, $varillasDisponibles, $espesorSierra);
        $jsonRespuesta = $usoVarillasCuadro->generarJson($resultado);

        if (!$jsonRespuesta['terminado']) {
            throw new \Exception('Ocurrió un error con las varillas');
        }

        $totalVarillas = 0;
        foreach ($jsonRespuesta['retazosUsados'] as $retazo) {
            $precioVentaDb = DB::table('stock_varillas as sv')
                ->join('materia_prima_varillas as mpv', 'sv.id_materia_prima_varilla', '=', 'mpv.id')
                ->where('sv.id', $retazo['id'])
                ->value('mpv.precioVenta');

            $precio = $retazo['mmUsados'] * $precioVentaDb;
            $totalVarillas += $precio;

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

        return $totalVarillas;
    }

    private function procesarTrupans($detalle, $cuadro)
    {
        $trupansDisponibles = StockTrupan::where('id_materia_prima_trupans', $cuadro['id_materia_prima_trupans'])
            ->where('stock', '>', 0)
            ->get()
            ->map(function ($item) {
                return [$item->id, $item->alto, $item->largo, $item->stock];
            })
            ->values()
            ->toArray();

        $usoLaminasCuadro = new UsoLaminasCuadro();
        $necesidadCuadro = [
            'largo' => $cuadro['lado_a'], 
            'ancho' => $cuadro['lado_b'], 
            'cantidad' => $cuadro['cantidad'], // Usar cantidad del JSON
            'nombre' => 'Cuadro'
        ];

        $respuesta = $usoLaminasCuadro->optimizarCuadro($necesidadCuadro, $trupansDisponibles);

        if (!$respuesta['terminado']) {
            throw new \Exception('Ocurrió un error con los trupans');
        }

        $materialId = $respuesta['material'];
        $areaUtilizada = $cuadro['lado_a'] * $cuadro['lado_b'];

        $precioVentaDb = DB::table('stock_trupans as st')
            ->join('materia_prima_trupans as mpt', 'st.id_materia_prima_trupans', '=', 'mpt.id')
            ->where('st.id', $materialId)
            ->value('mpt.precioVenta');

        $precio = $areaUtilizada * $precioVentaDb;

        MaterialesVentaPersonalizada::create([
            'stock_contorno_id' => null,
            'stock_trupan_id' => $materialId,
            'stock_vidrio_id' => null,
            'stock_varilla_id' => null,
            'cantidad' => 1,
            'precio_unitario' => $precio,
            'detalleVP_id' => $detalle->id
        ]);

        return $precio;
    }

    private function procesarVidrios($detalle, $cuadro)
    {
        $vidriosDisponibles = StockVidrio::where('id_materia_prima_vidrio', $cuadro['id_materia_prima_vidrios'])
            ->where('stock', '>', 0)
            ->get()
            ->map(function ($item) {
                return [$item->id, $item->alto, $item->largo, $item->stock];
            })
            ->values()
            ->toArray();

        $usoLaminasCuadro = new UsoLaminasCuadro();
        $necesidadCuadro = [
            'largo' => $cuadro['lado_a'], 
            'ancho' => $cuadro['lado_b'], 
            'cantidad' => $cuadro['cantidad'], 
            'nombre' => 'Cuadro'
        ];

        $respuesta = $usoLaminasCuadro->optimizarCuadro($necesidadCuadro, $vidriosDisponibles);

        if (!$respuesta['terminado']) {
            throw new \Exception('Ocurrió un error con los vidrios');
        }

        $materialId = $respuesta['material'];
        $areaUtilizada = $cuadro['lado_a'] * $cuadro['lado_b'];

        $precioVentaDb = DB::table('stock_vidrios as sv')
            ->join('materia_prima_vidrios as mpv', 'sv.id_materia_prima_vidrio', '=', 'mpv.id')
            ->where('sv.id', $materialId)
            ->value('mpv.precioVenta');

        $precio = $areaUtilizada * $precioVentaDb;

        MaterialesVentaPersonalizada::create([
            'stock_contorno_id' => null,
            'stock_trupan_id' => null,
            'stock_vidrio_id' => $materialId,
            'stock_varilla_id' => null,
            'cantidad' => 1,
            'precio_unitario' => $precio,
            'detalleVP_id' => $detalle->id
        ]);

        return $precio;
    }

    private function procesarContornos($detalle, $cuadro)
    {
        $contornosDisponibles = StockContorno::where('id_materia_prima_contorno', $cuadro['id_materia_prima_contornos'])
            ->where('stock', '>', 0)
            ->get()
            ->map(function ($item) {
                return [$item->id, $item->alto, $item->largo, $item->stock];
            })
            ->values()
            ->toArray();

        $usoLaminasCuadro = new UsoLaminasCuadro();
        $necesidadCuadro = [
            'largo' => $cuadro['lado_a'], 
            'ancho' => $cuadro['lado_b'], 
            'cantidad' => $cuadro['cantidad'], 
            'nombre' => 'Cuadro'
        ];

        $respuesta = $usoLaminasCuadro->optimizarCuadro($necesidadCuadro, $contornosDisponibles);

        if (!$respuesta['terminado']) {
            throw new \Exception('Ocurrió un error con los contornos');
        }

        $materialId = $respuesta['material'];
        $areaUtilizada = $cuadro['lado_a'] * $cuadro['lado_b'];

        $precioVentaDb = DB::table('stock_contornos as sc')
            ->join('materia_prima_contornos as mpc', 'sc.id_materia_prima_contorno', '=', 'mpc.id')
            ->where('sc.id', $materialId)
            ->value('mpc.precioVenta');

        $precio = $areaUtilizada * $precioVentaDb;

        MaterialesVentaPersonalizada::create([
            'stock_contorno_id' => $materialId,
            'stock_trupan_id' => null,
            'stock_vidrio_id' => null,
            'stock_varilla_id' => null,
            'cantidad' => 1,
            'precio_unitario' => $precio,
            'detalleVP_id' => $detalle->id
        ]);

        return $precio;
    }

    private function procesarPago($venta, $saldo, $precioTotal, $idFormaPago)
    {
        if ($saldo > $precioTotal) {
            $exceso = $saldo - $precioTotal;
            throw new \Exception("Hay un excedente de $exceso unidades en el pago");
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
}