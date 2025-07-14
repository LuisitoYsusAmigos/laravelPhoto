<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\VentaController;
use App\Models\Venta;
use Barryvdh\DomPDF\Facade\Pdf;

class GestionRecibosController extends Controller
{
    public function getHtml($id)
    {
        $venta = Venta::find($id);
        if (!$venta) {
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }
        // Usar VentaController para obtener la data
        $ventaJson = (new VentaController)->getVentaCompleta($id);
        $ventaArray = $ventaJson->getData(true)['venta'];

        return view('pdf.recibo', ['venta' => $ventaArray]);
    }

    public function getPdf($id)
    {
        $venta = Venta::find($id);
        if (!$venta) {
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }
        $ventaJson = (new VentaController)->getVentaCompleta($id);
        $ventaArray = $ventaJson->getData(true)['venta'];

        $pdf = Pdf::loadView('pdf.recibo', ['venta' => $ventaArray]);
        return $pdf->stream("recibo_{$ventaArray['id']}.pdf");
    }

    public function show($id)
    {
        $venta = Venta::find($id);

        if (!$venta) {
            return response()->json(['message' => 'Venta no encontrada'], 404);
        }

        return response()->json($venta);
    }
}
