<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\CalculosSistema;

class CotizadorController extends Controller
{
    public function calcular(Request $request, CalculosSistema $calculos)
    {
        $ancho = $request->query('ancho');
        $alto = $request->query('alto');
        $grosor = $request->query('grosor');

        // Validación manual: que todos sean números enteros positivos
        $errores = [];

        if (!ctype_digit($ancho)) {
            $errores['ancho'] = 'El ancho debe ser un número entero positivo.';
        }

        if (!ctype_digit($alto)) {
            $errores['alto'] = 'El alto debe ser un número entero positivo.';
        }

        if (!ctype_digit($grosor)) {
            $errores['grosor'] = 'El grosor debe ser un número entero positivo.';
        }

        if (!empty($errores)) {
            return response()->json([
                'success' => false,
                'errores' => $errores,
            ], 422);
        }

        // Convertimos los strings validados a int
        $ancho = (int) $ancho;
        $alto = (int) $alto;
        $grosor = (int) $grosor;

        $resultado = $calculos->calcularMargenExterno($ancho, $alto, $grosor);

        return response()->json([
            'success' => true,
            'datos' => $resultado,
        ]);
    }
}
