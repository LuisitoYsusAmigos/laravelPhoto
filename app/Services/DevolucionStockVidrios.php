<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVentaPersonalizada;
use App\Models\MaterialesVentaPersonalizada;
use App\Models\StockVidrio;
use Illuminate\Support\Facades\Log;

class DevolucionStockVidrios
{
    /**
     * Realiza la devolución de stock de vidrio asociadas a una venta.
     *
     * @param Venta $venta
     * @return void
     */
    public function devolucionStockVidrios(Venta $venta)
    {
        // 1. Obtener los detalles de venta personalizados asociados a esta venta
        $detallesPersonalizados = DetalleVentaPersonalizada::where('id_venta', $venta->id)->get();

        foreach ($detallesPersonalizados as $detalle) {
            // 2. Obtener los materiales de tipo vidrio asociados al detalle personalizado
            $materiales = MaterialesVentaPersonalizada::where('detalleVP_id', $detalle->id)
                ->whereNotNull('stock_vidrio_id')
                ->get();

            foreach ($materiales as $material) {
                $idStockVidrio = $material->stock_vidrio_id;
                $cantidad = $material->cantidad;

                $stockVidrio = StockVidrio::find($idStockVidrio);
                if ($stockVidrio) {
                    $stockVidrio->increment('stock', $cantidad);
                    Log::info("Stock de vidrio devuelto para stock ID: {$idStockVidrio}. Cantidad devuelta: {$cantidad}.");
                } else {
                    Log::warning("No se encontró el stock de vidrio con ID {$idStockVidrio} para realizar la devolución.");
                }
            }
        }
    }
}
