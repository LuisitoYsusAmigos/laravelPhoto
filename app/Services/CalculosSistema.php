<?php

namespace App\Services;

class CalculosSistema
{
    /**
     * Calcula dimensiones externas en milímetros usando enteros.
     *
     * @param int $anchoInterno
     * @param int $altoInterno
     * @param int $grosor
     * @return array
     */
    public function calcularMargenExterno(int $anchoInterno, int $altoInterno, int $grosor): array
    {
        $anchoExterno = $anchoInterno + 2 * $grosor;
        $altoExterno = $altoInterno + 2 * $grosor;
       // $largoTotal = ($anchoExterno + $altoExterno)*2+4; // Total length of the frame

        return [
            'ancho_externo' => $anchoExterno,
            'alto_externo' => $altoExterno,
           //'grosor' => $grosor,
           // 'largo_total' => $largoTotal,
        ];
    }
}
