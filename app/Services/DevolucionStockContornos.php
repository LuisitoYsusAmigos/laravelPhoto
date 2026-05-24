<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVentaPersonalizada;
use App\Models\MaterialesVentaPersonalizada;
use App\Models\StockContorno;
use Illuminate\Support\Facades\Log;

class DevolucionStockContornos
{
    /**
     * Realiza la devolución de stock de contornos asociadas a una venta.
     *
     * @param Venta $venta
     * @return void
     */
    public function devolucionStockContornos(Venta $venta)
    {
        // 1. Obtener los detalles de venta personalizados asociados a esta venta
        $detallesPersonalizados = DetalleVentaPersonalizada::where('id_venta', $venta->id)->get();

        foreach ($detallesPersonalizados as $detalle) {
            // 2. Obtener los materiales de tipo contorno asociados al detalle personalizado
            $materiales = MaterialesVentaPersonalizada::where('detalleVP_id', $detalle->id)
                ->whereNotNull('stock_contorno_id')
                ->get();

            foreach ($materiales as $material) {
                $idStockContorno = $material->stock_contorno_id;
                $cantidad = $material->cantidad;

                $stockContorno = StockContorno::find($idStockContorno);
                if ($stockContorno) {
                    $stockContorno->increment('stock', $cantidad);
                    Log::info("Stock de contorno devuelto para stock ID: {$idStockContorno}. Cantidad devuelta: {$cantidad}.");
                } else {
                    Log::warning("No se encontró el stock de contorno con ID {$idStockContorno} para realizar la devolución.");
                }
            }
        }
    }
}
