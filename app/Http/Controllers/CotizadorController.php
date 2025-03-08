<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
class CotizadorController extends Controller {
    public function index() {
        $cocha = 20;
        $espejo3MM = 0;
        $espejo2MM = 1;
        $a = 35;
        $b = 40;
        // Fin de constantes

        $tarija = $cocha + 3;
        $varilla = $tarija / 2.8;
        $calculoVarilla = ((((($a * 2) + ($b * 2)) / 100) * $varilla) * 1.6);
        $costoEspejo3mmArg = (((($a / 100) * ($b / 100)) * $espejo3MM) * 94.4) * 1.4;
        $costoEspejo2mm = (((((1 / 100) * (1 / 100)) * $espejo2MM) * 67.3) * 1.4);
        $subtotal = $calculoVarilla + $costoEspejo3mmArg + $costoEspejo2mm;
        $precioFinal = $subtotal * 2;

        return "precioFinal: $precioFinal";
    }
}
