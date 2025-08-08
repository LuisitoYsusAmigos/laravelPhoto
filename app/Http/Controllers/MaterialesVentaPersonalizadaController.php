<?php

namespace App\Http\Controllers;

use App\Models\MaterialesVentaPersonalizada;
use App\Models\StockContorno;
use App\Models\StockTrupan;
use App\Models\StockVidrio;
use App\Models\StockVarilla;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class MaterialesVentaPersonalizadaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $materiales = MaterialesVentaPersonalizada::with([
                'stockContorno.materiaPrimaContorno',
                'stockTrupan.materiaPrimaTrupan',
                'stockVidrio.materiaPrimaVidrio',
                'stockVarilla.materiaPrimaVarilla'
            ])->get();

            return response()->json([
                'success' => true,
                'data' => $materiales
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los materiales',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validación
        $validator = Validator::make($request->all(), [
            'stock_contorno_id' => 'nullable|exists:stock_contornos,id',
            'stock_trupan_id' => 'nullable|exists:stock_trupans,id',
            'stock_vidrio_id' => 'nullable|exists:stock_vidrios,id',
            'stock_varilla_id' => 'nullable|exists:stock_varillas,id',
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'nullable|integer|min:0',
            'detalleVP_id' => 'required|exists:detalle_venta_personalizadas,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 400);
        }

        // Validar que solo se seleccione un tipo de material
        $materialesSeleccionados = collect([
            $request->stock_contorno_id,
            $request->stock_trupan_id,
            $request->stock_vidrio_id,
            $request->stock_varilla_id
        ])->filter()->count();

        if ($materialesSeleccionados !== 1) {
            return response()->json([
                'success' => false,
                'message' => 'Debe seleccionar exactamente un tipo de material'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // Verificar stock disponible
            $stockDisponible = $this->verificarStockDisponible($request);
            if (!$stockDisponible['disponible']) {
                return response()->json([
                    'success' => false,
                    'message' => $stockDisponible['mensaje']
                ], 400);
            }

            // Crear el material
            $material = MaterialesVentaPersonalizada::create($request->all());

            // Descontar del stock
            $this->descontarStock($request);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Material creado exitosamente',
                'data' => $material->load([
                    'stockContorno.materiaPrimaContorno',
                    'stockTrupan.materiaPrimaTrupan',
                    'stockVidrio.materiaPrimaVidrio',
                    'stockVarilla.materiaPrimaVarilla'
                ])
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el material',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MaterialesVentaPersonalizada $materialesVentaPersonalizada)
    {
        try {
            $material = $materialesVentaPersonalizada->load([
                'stockContorno.materiaPrimaContorno',
                'stockTrupan.materiaPrimaTrupan',
                'stockVidrio.materiaPrimaVidrio',
                'stockVarilla.materiaPrimaVarilla'
            ]);

            return response()->json([
                'success' => true,
                'data' => $material
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el material',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MaterialesVentaPersonalizada $materialesVentaPersonalizada)
    {
        // Validación
        $validator = Validator::make($request->all(), [
            'cantidad' => 'required|integer|min:1',
            'precio_unitario' => 'nullable|integer|min:0'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 400);
        }

        DB::beginTransaction();
        try {
            $cantidadAnterior = $materialesVentaPersonalizada->cantidad;
            $diferenciaCantidad = $request->cantidad - $cantidadAnterior;

            // Si aumenta la cantidad, verificar stock disponible
            if ($diferenciaCantidad > 0) {
                $stockDisponible = $this->verificarStockDisponibleParaActualizacion($materialesVentaPersonalizada, $diferenciaCantidad);
                if (!$stockDisponible['disponible']) {
                    return response()->json([
                        'success' => false,
                        'message' => $stockDisponible['mensaje']
                    ], 400);
                }
            }

            // Actualizar el material
            $materialesVentaPersonalizada->update([
                'cantidad' => $request->cantidad,
                'precio_unitario' => $request->precio_unitario
            ]);

            // Ajustar stock
            $this->ajustarStock($materialesVentaPersonalizada, $diferenciaCantidad);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Material actualizado exitosamente',
                'data' => $materialesVentaPersonalizada->fresh()->load([
                    'stockContorno.materiaPrimaContorno',
                    'stockTrupan.materiaPrimaTrupan',
                    'stockVidrio.materiaPrimaVidrio',
                    'stockVarilla.materiaPrimaVarilla'
                ])
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el material',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MaterialesVentaPersonalizada $materialesVentaPersonalizada)
    {
        DB::beginTransaction();
        try {
            // Devolver stock antes de eliminar
            $this->devolverStock($materialesVentaPersonalizada);

            $materialesVentaPersonalizada->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Material eliminado exitosamente'
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el material',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verificar si hay stock disponible
     */
    private function verificarStockDisponible(Request $request)
    {
        if ($request->stock_contorno_id) {
            $stock = StockContorno::find($request->stock_contorno_id);
            $tipo = 'contorno';
        } elseif ($request->stock_trupan_id) {
            $stock = StockTrupan::find($request->stock_trupan_id);
            $tipo = 'trupan';
        } elseif ($request->stock_vidrio_id) {
            $stock = StockVidrio::find($request->stock_vidrio_id);
            $tipo = 'vidrio';
        } elseif ($request->stock_varilla_id) {
            $stock = StockVarilla::find($request->stock_varilla_id);
            $tipo = 'varilla';
        }

        if (!$stock) {
            return ['disponible' => false, 'mensaje' => 'Stock no encontrado'];
        }

        if ($stock->stock < $request->cantidad) {
            return [
                'disponible' => false,
                'mensaje' => "Stock insuficiente para {$tipo}. Disponible: {$stock->stock}, Solicitado: {$request->cantidad}"
            ];
        }

        return ['disponible' => true];
    }

    /**
     * Descontar del stock
     */
    private function descontarStock(Request $request)
    {
        if ($request->stock_contorno_id) {
            $stock = StockContorno::find($request->stock_contorno_id);
        } elseif ($request->stock_trupan_id) {
            $stock = StockTrupan::find($request->stock_trupan_id);
        } elseif ($request->stock_vidrio_id) {
            $stock = StockVidrio::find($request->stock_vidrio_id);
        } elseif ($request->stock_varilla_id) {
            $stock = StockVarilla::find($request->stock_varilla_id);
        }

        if ($stock) {
            $stock->decrement('stock', $request->cantidad);
        }
    }

    /**
     * Verificar stock para actualización
     */
    private function verificarStockDisponibleParaActualizacion(MaterialesVentaPersonalizada $material, int $diferenciaCantidad)
    {
        $stock = null;
        $tipo = '';

        if ($material->stock_contorno_id) {
            $stock = $material->stockContorno;
            $tipo = 'contorno';
        } elseif ($material->stock_trupan_id) {
            $stock = $material->stockTrupan;
            $tipo = 'trupan';
        } elseif ($material->stock_vidrio_id) {
            $stock = $material->stockVidrio;
            $tipo = 'vidrio';
        } elseif ($material->stock_varilla_id) {
            $stock = $material->stockVarilla;
            $tipo = 'varilla';
        }

        if (!$stock) {
            return ['disponible' => false, 'mensaje' => 'Stock no encontrado'];
        }

        if ($stock->stock < $diferenciaCantidad) {
            return [
                'disponible' => false,
                'mensaje' => "Stock insuficiente para {$tipo}. Disponible: {$stock->stock}, Adicional requerido: {$diferenciaCantidad}"
            ];
        }

        return ['disponible' => true];
    }

    /**
     * Ajustar stock (aumentar o disminuir)
     */
    private function ajustarStock(MaterialesVentaPersonalizada $material, int $diferenciaCantidad)
    {
        $stock = null;

        if ($material->stock_contorno_id) {
            $stock = $material->stockContorno;
        } elseif ($material->stock_trupan_id) {
            $stock = $material->stockTrupan;
        } elseif ($material->stock_vidrio_id) {
            $stock = $material->stockVidrio;
        } elseif ($material->stock_varilla_id) {
            $stock = $material->stockVarilla;
        }

        if ($stock && $diferenciaCantidad != 0) {
            $stock->decrement('stock', $diferenciaCantidad);
        }
    }

    /**
     * Devolver stock al eliminar
     */
    private function devolverStock(MaterialesVentaPersonalizada $material)
    {
        $stock = null;

        if ($material->stock_contorno_id) {
            $stock = $material->stockContorno;
        } elseif ($material->stock_trupan_id) {
            $stock = $material->stockTrupan;
        } elseif ($material->stock_vidrio_id) {
            $stock = $material->stockVidrio;
        } elseif ($material->stock_varilla_id) {
            $stock = $material->stockVarilla;
        }

        if ($stock) {
            $stock->increment('stock', $material->cantidad);
        }
    }
}