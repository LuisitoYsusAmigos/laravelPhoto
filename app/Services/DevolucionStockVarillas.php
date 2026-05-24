<?php

namespace App\Services;

use App\Models\Venta;
use App\Models\DetalleVentaPersonalizada;
use App\Models\MaterialesVentaPersonalizada;
use App\Models\StockVarilla;
use Illuminate\Support\Facades\Log;

class DevolucionStockVarillas
{
    /**
     * Realiza la devolución de stock de varillas asociadas a una venta.
     *
     * @param Venta $venta
     * @return void
     */
    public function devolucionStockVarillas(Venta $venta)
    {
        // 1. Obtener los detalles de venta personalizados asociados a esta venta
        $detallesPersonalizados = DetalleVentaPersonalizada::where('id_venta', $venta->id)->get();

        foreach ($detallesPersonalizados as $detalle) {
            // 2. Obtener los materiales de tipo varilla asociados al detalle personalizado
            $materiales = MaterialesVentaPersonalizada::where('detalleVP_id', $detalle->id)
                ->whereNotNull('stock_varilla_id')
                ->get();

            foreach ($materiales as $material) {
                $idStockVarilla = $material->stock_varilla_id;
                $cantidad = $material->cantidad;

                $stockVarilla = StockVarilla::find($idStockVarilla);
                if ($stockVarilla) {
                    $stockVarilla->increment('stock', $cantidad);
                    Log::info("Stock de varilla devuelto para stock ID: {$idStockVarilla}. Cantidad devuelta: {$cantidad}.");
                } else {
                    Log::warning("No se encontró el stock de varilla con ID {$idStockVarilla} para realizar la devolución.");
                }
            }
        }
    }
}
