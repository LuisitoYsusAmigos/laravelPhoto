<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVentaPersonalizada;
use App\Models\MaterialesVentaPersonalizada;
use App\Models\StockTrupan;
use Illuminate\Support\Facades\Log;

class DevolucionStockTrupans
{
    /**
     * Realiza la devolución de stock de trupan asociadas a una venta.
     *
     * @param Venta $venta
     * @return void
     */
    public function devolucionStockTrupans(Venta $venta)
    {
        // 1. Obtener los detalles de venta personalizados asociados a esta venta
        $detallesPersonalizados = DetalleVentaPersonalizada::where('id_venta', $venta->id)->get();

        foreach ($detallesPersonalizados as $detalle) {
            // 2. Obtener los materiales de tipo trupan asociados al detalle personalizado
            $materiales = MaterialesVentaPersonalizada::where('detalleVP_id', $detalle->id)
                ->whereNotNull('stock_trupan_id')
                ->get();

            foreach ($materiales as $material) {
                $idStockTrupan = $material->stock_trupan_id;
                $cantidad = $material->cantidad;

                $stockTrupan = StockTrupan::find($idStockTrupan);
                if ($stockTrupan) {
                    $stockTrupan->increment('stock', $cantidad);
                    Log::info("Stock de trupan devuelto para stock ID: {$idStockTrupan}. Cantidad devuelta: {$cantidad}.");
                } else {
                    Log::warning("No se encontró el stock de trupan con ID {$idStockTrupan} para realizar la devolución.");
                }
            }
        }
    }
}
