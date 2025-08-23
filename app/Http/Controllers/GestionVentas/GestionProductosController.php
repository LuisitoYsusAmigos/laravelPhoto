<?php

namespace App\Http\Controllers\GestionVentas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Producto;
use App\Models\DetalleVentaProducto;

class GestionProductosController extends Controller
{
    /**
     * Procesa los productos regulares de una venta
     * 
     * @param $venta Modelo de venta
     * @param array $detalles Array de productos con idProducto y cantidad
     * @return float Total de productos procesados
     * @throws \Exception Si hay problemas con stock o productos
     */
    public function procesarProductos($venta, array $detalles)
    {
        $totalProductos = 0;

        foreach ($detalles as $item) {
            $producto = $this->validarProducto($item['idProducto']);
            $this->validarStock($producto, $item['cantidad']);
            
            $totalProducto = $this->procesarProductoIndividual($venta, $producto, $item['cantidad']);
            $totalProductos += $totalProducto;
        }

        return $totalProductos;
    }

    /**
     * Valida que el producto exista
     */
    private function validarProducto($idProducto)
    {
        $producto = Producto::find($idProducto);

        if (!$producto) {
            throw new \Exception("Producto ID {$idProducto} no encontrado");
        }

        return $producto;
    }

    /**
     * Valida que haya suficiente stock
     */
    private function validarStock($producto, $cantidadSolicitada)
    {
        if ($producto->stock_global_actual < $cantidadSolicitada) {
            throw new \Exception("Stock insuficiente para el producto ID {$producto->id}: {$producto->descripcion}. Stock disponible: {$producto->stock_global_actual}, solicitado: {$cantidadSolicitada}");
        }
    }

    /**
     * Procesa un producto individual aplicando lógica FIFO
     */
    private function procesarProductoIndividual($venta, $producto, $cantidad)
    {
        $cantidadRestante = $cantidad;
        $precioUnitario = $producto->precioVenta;
        $totalProducto = $precioUnitario * $cantidad;

        $lotes = $this->obtenerLotesDisponibles($producto->id);

        foreach ($lotes as $lote) {
            if ($cantidadRestante <= 0) break;

            $cantidadUsada = $this->procesarLote($venta, $producto, $lote, $cantidadRestante);
            $cantidadRestante -= $cantidadUsada;
        }

        if ($cantidadRestante > 0) {
            throw new \Exception("No se pudo descontar todo el stock necesario para el producto ID {$producto->id}. Cantidad restante: {$cantidadRestante}");
        }

        return $totalProducto;
    }

    /**
     * Obtiene los lotes disponibles ordenados por FIFO
     */
    private function obtenerLotesDisponibles($idProducto)
    {
        return DB::table('stock_productos')
            ->where('id_producto', $idProducto)
            ->where('contable', 1)
            ->where('stock', '>', 0)
            ->orderBy('id') // FIFO - First In, First Out
            ->get();
    }

    /**
     * Procesa un lote específico de stock
     */
    private function procesarLote($venta, $producto, $lote, $cantidadRestante)
    {
        $cantidadAUsar = min($cantidadRestante, $lote->stock);
        
        // Calcular el precio total para esta cantidad específica
        $precioTotal = $lote->precio * $cantidadAUsar;

        // Actualizar stock del lote
        DB::table('stock_productos')
            ->where('id', $lote->id)
            ->decrement('stock', $cantidadAUsar);

        // Crear detalle de venta CON EL PRECIO TOTAL
        DetalleVentaProducto::create([
            'idVenta' => $venta->id,
            'idProducto' => $producto->id,
            'id_stock_producto' => $lote->id,
            'cantidad' => $cantidadAUsar,
            'precio' => $precioTotal, // Precio total en lugar de precio unitario
        ]);

        return $cantidadAUsar;
    }

    /**
     * Obtiene información de stock de un producto
     */
    public function obtenerInfoStock($idProducto)
    {
        $producto = $this->validarProducto($idProducto);
        $lotes = $this->obtenerLotesDisponibles($idProducto);

        return [
            'producto' => $producto,
            'stock_total' => $producto->stock_global_actual,
            'lotes_disponibles' => $lotes->count(),
            'detalle_lotes' => $lotes
        ];
    }

    /**
     * Verifica si se puede procesar una lista de productos
     */
    public function verificarDisponibilidad(array $detalles)
    {
        $resultados = [];

        foreach ($detalles as $item) {
            try {
                $producto = $this->validarProducto($item['idProducto']);
                $this->validarStock($producto, $item['cantidad']);
                
                $resultados[] = [
                    'idProducto' => $item['idProducto'],
                    'disponible' => true,
                    'stock_actual' => $producto->stock_global_actual,
                    'cantidad_solicitada' => $item['cantidad']
                ];
            } catch (\Exception $e) {
                $resultados[] = [
                    'idProducto' => $item['idProducto'],
                    'disponible' => false,
                    'error' => $e->getMessage(),
                    'cantidad_solicitada' => $item['cantidad']
                ];
            }
        }

        return $resultados;
    }

    // ============== MÉTODOS DE DEVOLUCIÓN ==============

    /**
     * Devuelve el stock de productos regulares de una venta
     * 
     * @param $detalleProductos Colección de detalles de productos
     * @throws \Exception Si hay problemas al devolver stock
     */
    public function devolverStockProductos($detalleProductos)
    {
        foreach ($detalleProductos as $detalle) {
            try {
                // Verificar que el lote de stock existe
                $stockExiste = DB::table('stock_productos')
                    ->where('id', $detalle->id_stock_producto)
                    ->exists();

                if (!$stockExiste) {
                    throw new \Exception("Lote de stock ID {$detalle->id_stock_producto} no encontrado para devolución");
                }

                // Devolver stock al lote específico usado
                DB::table('stock_productos')
                    ->where('id', $detalle->id_stock_producto)
                    ->increment('stock', $detalle->cantidad);

                // Log para auditoría
                Log::info("Stock producto devuelto", [
                    'producto_id' => $detalle->idProducto,
                    'lote_id' => $detalle->id_stock_producto,
                    'cantidad_devuelta' => $detalle->cantidad,
                    'venta_id' => $detalle->idVenta
                ]);

            } catch (\Exception $e) {
                throw new \Exception("Error al devolver stock del producto ID {$detalle->idProducto}: " . $e->getMessage());
            }
        }
    }

    /**
     * Elimina los detalles de productos de una venta
     * 
     * @param int $idVenta ID de la venta
     * @return int Cantidad de detalles eliminados
     */
    public function eliminarDetallesProductos($idVenta)
    {
        $detallesEliminados = DetalleVentaProducto::where('idVenta', $idVenta)->delete();
        
        Log::info("Detalles de productos eliminados", [
            'venta_id' => $idVenta,
            'cantidad_eliminados' => $detallesEliminados
        ]);

        return $detallesEliminados;
    }

    /**
     * Obtiene información detallada de los productos de una venta para devolución
     * 
     * @param int $idVenta ID de la venta
     * @return array Información de productos y stock a devolver
     */
    public function obtenerInfoDevolucionProductos($idVenta)
    {
        $detalles = DetalleVentaProducto::with('producto')
            ->where('idVenta', $idVenta)
            ->get();

        $infoDevolucion = [];

        foreach ($detalles as $detalle) {
            $infoDevolucion[] = [
                'producto_id' => $detalle->idProducto,
                'producto_nombre' => $detalle->producto->descripcion ?? 'N/A',
                'lote_id' => $detalle->id_stock_producto,
                'cantidad_a_devolver' => $detalle->cantidad,
                'precio_total' => $detalle->precio
            ];
        }

        return [
            'total_productos_diferentes' => $detalles->count(),
            'productos_detalle' => $infoDevolucion,
            'valor_total_productos' => $detalles->sum('precio')
        ];
    }
}