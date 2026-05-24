<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVentaProducto;
use App\Models\StockProducto;
use Illuminate\Support\Facades\Log;

class DevolucionStockProductos
{
    /**
     * Realiza la devolución de stock de productos asociados a una venta.
     *
     * @param Venta $venta
     * @return void
     */
    public function devolucionStockProductos(Venta $venta)
    {
        // 1. Recuperar todos los registros de detalle de venta producto que tengan el ID de esta venta
        $detalles = DetalleVentaProducto::where('idVenta', $venta->id)->get();

        // 2. Iterar sobre cada detalle para volver a sumar la cantidad al stock correspondiente
        foreach ($detalles as $detalle) {
            $idProducto = $detalle->idProducto;
            $idStockProducto = $detalle->id_stock_producto; // ID del stock del producto
            $cantidad = $detalle->cantidad;

            if ($idStockProducto) {
                $stockProducto = StockProducto::find($idStockProducto);

                if ($stockProducto) {
                    // Volver a sumar la cantidad al stock original
                    $stockProducto->increment('stock', $cantidad);

                    Log::info("Stock devuelto para el producto ID: {$idProducto}, stock ID: {$idStockProducto}. Cantidad devuelta: {$cantidad}.");
                } else {
                    Log::warning("No se encontró el stock del producto con ID {$idStockProducto} para realizar la devolución.");
                }
            }
        }
    }
}
