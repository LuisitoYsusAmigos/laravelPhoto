<?php
namespace App\Services;


class UsoVarillasCuadro
{

    /**
     * Optimiza el corte de varillas usando First Fit Decreasing (FFD).
     * Complejidad O(P × R) en lugar del backtracking O(2^P) anterior.
     *
     * @param  array  $necesidades        [{largo, ancho, cantidad, nombre}]
     * @param  array  $retazosDisponibles [[id, largo, cantidad], ...]
     * @param  float  $espesorSierra      Kerf entre cortes (mm)
     */
    public function optimizarCorte($necesidades, $retazosDisponibles, $espesorSierra = 0.0)
    {
        // Expandir retazos: cada unidad de stock se convierte en un bin potencial
        $poolDisponible = $this->expandirRetazos($retazosDisponibles);

        // Generar piezas y ordenarlas de mayor a menor (First Fit Decreasing)
        $piezasPendientes = $this->generarListaPiezas($necesidades);
        usort($piezasPendientes, function ($a, $b) {
            return $b['largo'] <=> $a['largo'];
        });

        // Bins abiertos: cada bin = 1 retazo físico en uso
        // [ 'retazo' => [...], 'piezas' => [...], 'espacioUsado' => float ]
        $bins          = [];
        $piezasRestantes = [];

        foreach ($piezasPendientes as $pieza) {
            $asignado = false;

            // ── Best-fit en bins ya abiertos ────────────────────────────────
            $mejorBinIdx  = -1;
            $menorEspacio = PHP_INT_MAX;

            foreach ($bins as $idx => $bin) {
                $kerf       = count($bin['piezas']) > 0 ? $espesorSierra : 0;
                $necesario  = $pieza['largo'] + $kerf;
                $disponible = $bin['retazo'][1] - $bin['espacioUsado'];

                if ($disponible >= $necesario && $disponible < $menorEspacio) {
                    $menorEspacio = $disponible;
                    $mejorBinIdx  = $idx;
                }
            }

            if ($mejorBinIdx !== -1) {
                $kerf = count($bins[$mejorBinIdx]['piezas']) > 0 ? $espesorSierra : 0;
                $bins[$mejorBinIdx]['piezas'][]      = $pieza;
                $bins[$mejorBinIdx]['espacioUsado'] += $pieza['largo'] + $kerf;
                $asignado = true;
            } else {
                // ── Abrir el retazo más pequeño del pool que aún quepa la pieza ──
                $mejorPoolIdx  = -1;
                $menorLargoFit = PHP_INT_MAX;

                foreach ($poolDisponible as $pidx => $retazoDisp) {
                    if ($retazoDisp[1] >= $pieza['largo'] && $retazoDisp[1] < $menorLargoFit) {
                        $menorLargoFit = $retazoDisp[1];
                        $mejorPoolIdx  = $pidx;
                    }
                }

                if ($mejorPoolIdx !== -1) {
                    $nuevoRetazo = $poolDisponible[$mejorPoolIdx];
                    unset($poolDisponible[$mejorPoolIdx]);
                    $poolDisponible = array_values($poolDisponible);

                    $bins[] = [
                        'retazo'      => $nuevoRetazo,
                        'piezas'      => [$pieza],
                        'espacioUsado' => $pieza['largo'],  // primer pieza: sin kerf
                    ];
                    $asignado = true;
                }
            }

            if (!$asignado) {
                $piezasRestantes[] = $pieza;
            }
        }

        // Convertir bins al formato retazosUsados esperado por el resto del sistema
        $retazosUsados = [];
        foreach ($bins as $bin) {
            $retazo      = $bin['retazo'];
            $desperdicio = $retazo[1] - $bin['espacioUsado'];

            $retazosUsados[] = [
                'idUnico'      => $retazo[0],
                'idOriginal'   => $retazo[2],
                'numeroUnidad' => $retazo[3],
                'largo'        => $retazo[1],
                'piezas'       => $bin['piezas'],
                'desperdicio'  => $desperdicio,
                'eficiencia'   => $retazo[1] > 0
                    ? (($retazo[1] - $desperdicio) / $retazo[1]) * 100
                    : 0,
            ];
        }

        return [
            'retazosUsados'    => $retazosUsados,
            'piezasFaltantes'  => count($piezasRestantes),
            'piezasPendientes' => $piezasRestantes,
            'resumen'          => $this->generarResumen($retazosUsados, $piezasRestantes),
            'retazosSobrantes' => $this->calcularSobrantes($retazosDisponibles, $retazosUsados),
        ];
    }

    /**
     * Analiza la factibilidad del corte
     */
    public function analizarFactibilidad($necesidades, $retazosDisponibles, $espesorSierra = 0.3)
    {
        echo "ANÁLISIS DE FACTIBILIDAD:\n";
        echo "========================================\n\n";

        // Mostrar inventario disponible
        echo "📦 INVENTARIO DISPONIBLE:\n";
        foreach ($retazosDisponibles as $retazo) {
            echo "   • ID:{$retazo[0]} - {$retazo[1]}cm (Cantidad: {$retazo[2]} unidades)\n";
        }
        echo "\n";

        foreach ($necesidades as $necesidad) {
            $totalPorCuadro = ($necesidad['largo'] * 2) + ($necesidad['ancho'] * 2);
            $totalConCortes = $totalPorCuadro + ($espesorSierra * 3);

            echo "🖼️  {$necesidad['nombre']}: {$necesidad['largo']}x{$necesidad['ancho']} cm\n";
            echo "   📏 Piezas: {$necesidad['largo']}+{$necesidad['largo']}+{$necesidad['ancho']}+{$necesidad['ancho']} = {$totalPorCuadro}cm\n";
            echo "   ✂️  Con cortes: {$totalConCortes}cm\n";

            $retazosCompatibles = array_filter($retazosDisponibles, function ($retazo) use ($totalConCortes) {
                return $retazo[1] >= $totalConCortes && $retazo[2] > 0;
            });

            if (!empty($retazosCompatibles)) {
                // Ordenar por longitud
                usort($retazosCompatibles, function ($a, $b) {
                    return $a[1] <=> $b[1];
                });

                $retazosTexto = array_map(function ($retazo) {
                    return "ID:{$retazo[0]}({$retazo[1]}cm x{$retazo[2]})";
                }, $retazosCompatibles);

                echo "   ✅ Retazos compatibles: " . implode(", ", $retazosTexto) . "\n";

                $mejorRetazo = $retazosCompatibles[0];
                $desperdicio = $mejorRetazo[1] - $totalConCortes;
                $eficiencia = (($mejorRetazo[1] - $desperdicio) / $mejorRetazo[1]) * 100;

                // Calcular cuántos cuadros se pueden hacer con este tipo de retazo
                $cuadrosPosibles = min($mejorRetazo[2], $necesidad['cantidad']);

                if ($desperdicio == 0) {
                    echo "   🎯 PERFECTO: Retazo ID:{$mejorRetazo[0]} de {$mejorRetazo[1]}cm (sin desperdicio)\n";
                } else {
                    echo "   💡 ÓPTIMO: Retazo ID:{$mejorRetazo[0]} de {$mejorRetazo[1]}cm (desperdicio: {$desperdicio}cm, eficiencia: " .
                        round($eficiencia, 1) . "%)\n";
                }
                echo "   📊 Puedes hacer {$cuadrosPosibles} de {$necesidad['cantidad']} cuadros con este retazo\n";
            } else {
                echo "   ❌ Ningún retazo disponible puede hacer 1 cuadro completo\n";
            }
            echo "\n";
        }

        echo "========================================\n\n";
    }

    /**
     * Muestra resultados completos
     */
    public function mostrarResultados($resultado)
    {
        $this->mostrarResumen($resultado['resumen']);
        $this->mostrarDetalleCortes($resultado['retazosUsados']);
        $this->mostrarPiezasFaltantes($resultado['piezasPendientes']);
        $this->mostrarJson($resultado);
    }

    // === MÉTODOS PRIVADOS ===

    /**
     * Expande retazos según cantidad disponible
     */
    private function expandirRetazos($retazosDisponibles)
    {
        $retazosExpandidos = [];
        $contadorUnico = 0;

        foreach ($retazosDisponibles as $retazo) {
            $id = $retazo[0];
            $largo = $retazo[1];
            $cantidad = $retazo[2];

            for ($i = 0; $i < $cantidad; $i++) {
                $retazosExpandidos[] = [
                    $contadorUnico++, // ID único para cada unidad
                    $largo,          // Longitud
                    $id,             // ID original del tipo
                    ($i + 1)         // Número de unidad de este tipo
                ];
            }
        }

        return $retazosExpandidos;
    }

    /**
     * Calcula retazos sobrantes
     */
    private function calcularSobrantes($retazosOriginales, $retazosUsados)
    {
        $sobrantes = [];

        foreach ($retazosOriginales as $original) {
            $idOriginal = $original[0];
            $largo = $original[1];
            $cantidadTotal = $original[2];

            // Contar cuántos se usaron de este tipo
            $usados = 0;
            foreach ($retazosUsados as $usado) {
                if ($usado['idOriginal'] == $idOriginal) {
                    $usados++;
                }
            }

            $sobrante = $cantidadTotal - $usados;

            if ($sobrante > 0) {
                $sobrantes[] = [
                    'id' => $idOriginal,
                    'largo' => $largo,
                    'cantidad' => $sobrante,
                    'cantidadOriginal' => $cantidadTotal,
                    'cantidadUsada' => $usados
                ];
            }
        }

        return $sobrantes;
    }

    // encontrarMejorCorte, optimizarCombinaciones y buscarMejorCombinacion
    // fueron eliminados: el backtracking O(2^N) fue reemplazado por FFD en optimizarCorte.

    private function generarListaPiezas($necesidades)
    {
        $piezas = [];

        foreach ($necesidades as $necesidad) {
            $largo = $necesidad['largo'];
            $ancho = $necesidad['ancho'];
            $cantidad = $necesidad['cantidad'];
            $nombre = $necesidad['nombre'] ?? "Cuadro {$largo}x{$ancho}";

            for ($i = 0; $i < $cantidad; $i++) {
                $piezas[] = ['largo' => $largo, 'tipo' => 'horizontal', 'origen' => $nombre . " #" . ($i + 1)];
                $piezas[] = ['largo' => $largo, 'tipo' => 'horizontal', 'origen' => $nombre . " #" . ($i + 1)];
                $piezas[] = ['largo' => $ancho, 'tipo' => 'vertical', 'origen' => $nombre . " #" . ($i + 1)];
                $piezas[] = ['largo' => $ancho, 'tipo' => 'vertical', 'origen' => $nombre . " #" . ($i + 1)];
            }
        }

        return $piezas;
    }

    private function generarResumen($retazosUsados, $piezasFaltantes)
    {
        $totalRetazos = count($retazosUsados);
        $totalDesperdicio = array_sum(array_column($retazosUsados, 'desperdicio'));
        $totalMaderaUsada = 0;
        $totalPiezasCortadas = 0;

        foreach ($retazosUsados as $retazo) {
            $totalMaderaUsada += $retazo['largo'];
            $totalPiezasCortadas += count($retazo['piezas']);
        }

        $eficienciaPromedio = $totalMaderaUsada > 0 ?
            (($totalMaderaUsada - $totalDesperdicio) / $totalMaderaUsada) * 100 : 0;

        return [
            'totalRetazosUsados' => $totalRetazos,
            'totalPiezasCortadas' => $totalPiezasCortadas,
            'totalPiezasFaltantes' => count($piezasFaltantes),
            'totalMaderaUsada' => $totalMaderaUsada,
            'totalDesperdicio' => $totalDesperdicio,
            'eficienciaPromedio' => round($eficienciaPromedio, 2),
            'desperdicioPromedio' => $totalRetazos > 0 ? round($totalDesperdicio / $totalRetazos, 2) : 0,
            'cortesPerfeitos' => count(array_filter($retazosUsados, function ($r) {
                return $r['desperdicio'] == 0;
            }))
        ];
    }

    private function mostrarResumen($resumen)
    {
        echo "\nRESUMEN GENERAL:\n";
        echo "========================================\n";
        echo "📊 Total de retazos utilizados: " . $resumen['totalRetazosUsados'] . "\n";
        echo "✂️  Total de piezas cortadas: " . $resumen['totalPiezasCortadas'] . "\n";
        echo "❗ Total de piezas faltantes: " . $resumen['totalPiezasFaltantes'] . "\n";
        echo "📏 Madera total utilizada: " . $resumen['totalMaderaUsada'] . " cm\n";
        echo "🗑️  Desperdicio total: " . $resumen['totalDesperdicio'] . " cm\n";
        echo "⚡ Eficiencia promedio: " . $resumen['eficienciaPromedio'] . "%\n";
        echo "🎯 Cortes perfectos: " . $resumen['cortesPerfeitos'] . "\n";
        echo "========================================\n";
    }

    private function mostrarDetalleCortes($retazosUsados)
    {
        if (empty($retazosUsados)) {
            echo "\n❌ No se pudieron realizar cortes.\n";
            return;
        }

        echo "\nDETALLE DE CORTES REALIZADOS:\n";
        echo "========================================\n";

        foreach ($retazosUsados as $indice => $retazo) {
            $numeroCorte = $indice + 1;
            $estado = $retazo['desperdicio'] == 0 ? "🎯 PERFECTO" : "⚡ CON DESPERDICIO";

            echo "\n🪵 CORTE #{$numeroCorte} - Retazo ID:{$retazo['idOriginal']} Unidad #{$retazo['numeroUnidad']} ({$retazo['largo']} cm) - {$estado}\n";
            echo "   Eficiencia: " . round($retazo['eficiencia'], 1) . "%\n";
            echo "   Piezas cortadas:\n";

            foreach ($retazo['piezas'] as $pieza) {
                echo "   ✂️  {$pieza['largo']} cm [{$pieza['tipo']}] → {$pieza['origen']}\n";
            }

            echo "   🗑️  Desperdicio: {$retazo['desperdicio']} cm\n";
            echo "----------------------------------------\n";
        }
    }

    private function mostrarPiezasFaltantes($piezasPendientes)
    {
        if (empty($piezasPendientes)) {
            echo "\n🎉 ¡TODAS LAS PIEZAS FUERON CORTADAS EXITOSAMENTE!\n";
            return;
        }

        echo "\n❗ PIEZAS PENDIENTES DE CORTAR:\n";
        echo "========================================\n";
        echo "⚠️  No se pudieron cortar " . count($piezasPendientes) . " piezas:\n\n";

        foreach ($piezasPendientes as $pieza) {
            echo "   • {$pieza['largo']} cm [{$pieza['tipo']}] - {$pieza['origen']}\n";
        }
        echo "\n";
    }

    /**
     * Genera y muestra JSON con formato específico
     */
    private function mostrarJson($resultado)
    {
        echo "\n📋 RESUMEN JSON:\n";
        echo "========================================\n";

        $json = $this->generarJson($resultado);
        echo json_encode($json, JSON_PRETTY_PRINT) . "\n";
        echo "========================================\n";
    }
    /**
     * Genera JSON con formato específico (para devolver como variable)
     */
    /**
     * Genera JSON con formato específico (para devolver como variable)
     */
    public function generarJson($resultado)
    {
        // Determinar si se terminó todo (no hay piezas faltantes)
        $terminado = $resultado['piezasFaltantes'] == 0;

        // Consolidar retazos utilizados por ID con información detallada
        $retazosConsolidados = [];

        foreach ($resultado['retazosUsados'] as $retazo) {
            $idOriginal = $retazo['idOriginal'];

            if (!isset($retazosConsolidados[$idOriginal])) {
                $retazosConsolidados[$idOriginal] = [
                    'cantidad' => 0,
                    'mmUsados' => 0
                ];
            }

            $retazosConsolidados[$idOriginal]['cantidad']++;

            // Calcular milímetros utilizados de este retazo
            $mmUtilizados = ($retazo['largo'] - $retazo['desperdicio']) * 10;
            $retazosConsolidados[$idOriginal]['mmUsados'] += $mmUtilizados;
        }

        // Convertir a formato requerido
        $retazosArray = [];
        foreach ($retazosConsolidados as $id => $datos) {
            $retazosArray[] = [
                'id' => $id,
                'cantidad' => $datos['cantidad'],
                'mmUsados' => $datos['mmUsados']
            ];
        }

        // Generar JSON final
        return [
            'terminado' => $terminado,
            'retazosUsados' => $retazosArray
        ];
    }


    /*
    public function generarJson($resultado) {
        // Determinar si se terminó todo (no hay piezas faltantes)
        $terminado = $resultado['piezasFaltantes'] == 0;

        // Consolidar retazos utilizados por ID
        $retazosConsolidados = [];

        foreach ($resultado['retazosUsados'] as $retazo) {
            $idOriginal = $retazo['idOriginal'];

            if (!isset($retazosConsolidados[$idOriginal])) {
                $retazosConsolidados[$idOriginal] = 0;
            }

            $retazosConsolidados[$idOriginal]++;
        }

        // Convertir a formato requerido
        $retazosArray = [];
        foreach ($retazosConsolidados as $id => $cantidad) {
            $retazosArray[] = [
                'id' => $id,
                'cantidad' => $cantidad
            ];
        }

        // Generar JSON final
        return [
            'terminado' => $terminado,
            'retazosUsados' => $retazosArray
        ];
    }



    */
}

?>