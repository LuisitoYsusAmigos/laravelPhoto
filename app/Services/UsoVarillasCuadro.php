<?php
namespace App\Services;


class UsoVarillasCuadro {
    
    public function optimizarCorte($necesidades, $retazosDisponibles, $espesorSierra = 0.3) {
        // Expandir retazos según cantidad disponible
        $retazosExpandidos = $this->expandirRetazos($retazosDisponibles);
        
        $piezasPendientes = $this->generarListaPiezas($necesidades);
        
        usort($piezasPendientes, function($a, $b) {
            return $b['largo'] <=> $a['largo'];
        });
        
        $retazosUsados = [];
        $piezasRestantes = $piezasPendientes;
        
        while (!empty($piezasRestantes) && !empty($retazosExpandidos)) {
            $mejorCorte = $this->encontrarMejorCorte($piezasRestantes, $retazosExpandidos, $espesorSierra);
            
            if ($mejorCorte === null) {
                break;
            }
            
            $retazosUsados[] = $mejorCorte['corte'];
            $piezasRestantes = $mejorCorte['piezasRestantes'];
            
            $retazosExpandidos = array_filter($retazosExpandidos, function($retazo) use ($mejorCorte) {
                return $retazo[0] != $mejorCorte['corte']['idUnico'];
            });
            $retazosExpandidos = array_values($retazosExpandidos);
        }
        
        return [
            'retazosUsados' => $retazosUsados,
            'piezasFaltantes' => count($piezasRestantes),
            'piezasPendientes' => $piezasRestantes,
            'resumen' => $this->generarResumen($retazosUsados, $piezasRestantes),
            'retazosSobrantes' => $this->calcularSobrantes($retazosDisponibles, $retazosUsados)
        ];
    }
    
    /**
     * Analiza la factibilidad del corte
     */
    public function analizarFactibilidad($necesidades, $retazosDisponibles, $espesorSierra = 0.3) {
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
            
            $retazosCompatibles = array_filter($retazosDisponibles, function($retazo) use ($totalConCortes) {
                return $retazo[1] >= $totalConCortes && $retazo[2] > 0;
            });
            
            if (!empty($retazosCompatibles)) {
                // Ordenar por longitud
                usort($retazosCompatibles, function($a, $b) {
                    return $a[1] <=> $b[1];
                });
                
                $retazosTexto = array_map(function($retazo) {
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
    public function mostrarResultados($resultado) {
        $this->mostrarResumen($resultado['resumen']);
        $this->mostrarDetalleCortes($resultado['retazosUsados']);
        $this->mostrarPiezasFaltantes($resultado['piezasPendientes']);
        $this->mostrarJson($resultado);
    }
    
    // === MÉTODOS PRIVADOS ===
    
    /**
     * Expande retazos según cantidad disponible
     */
    private function expandirRetazos($retazosDisponibles) {
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
    private function calcularSobrantes($retazosOriginales, $retazosUsados) {
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
    
    private function encontrarMejorCorte($piezasPendientes, $retazosExpandidos, $espesorSierra) {
        $mejorCorte = null;
        $menorDesperdicio = PHP_INT_MAX;
        $mayorPiezasCortadas = 0;
        
        foreach ($retazosExpandidos as $retazo) {
            $idUnico = $retazo[0];
            $largo = $retazo[1];
            $idOriginal = $retazo[2];
            $numeroUnidad = $retazo[3];
            
            $resultado = $this->optimizarCombinaciones($largo, $piezasPendientes, $espesorSierra);
            
            if (!empty($resultado['piezasCortadas'])) {
                $desperdicio = $resultado['desperdicio'];
                $numPiezas = count($resultado['piezasCortadas']);
                
                $esMejor = false;
                if ($desperdicio < $menorDesperdicio) {
                    $esMejor = true;
                } elseif ($desperdicio == $menorDesperdicio && $numPiezas > $mayorPiezasCortadas) {
                    $esMejor = true;
                }
                
                if ($esMejor) {
                    $menorDesperdicio = $desperdicio;
                    $mayorPiezasCortadas = $numPiezas;
                    
                    $mejorCorte = [
                        'corte' => [
                            'idUnico' => $idUnico,
                            'idOriginal' => $idOriginal,
                            'numeroUnidad' => $numeroUnidad,
                            'largo' => $largo,
                            'piezas' => $resultado['piezasCortadas'],
                            'desperdicio' => $desperdicio,
                            'eficiencia' => (($largo - $desperdicio) / $largo) * 100
                        ],
                        'piezasRestantes' => $resultado['piezasRestantes']
                    ];
                    
                    if ($desperdicio == 0) {
                        break;
                    }
                }
            }
        }
        
        return $mejorCorte;
    }
    
    private function optimizarCombinaciones($retazoLargo, $piezasPendientes, $espesorSierra) {
        $n = count($piezasPendientes);
        
        $mejorCombinacion = $this->buscarMejorCombinacion(
            $piezasPendientes, 
            $retazoLargo, 
            $espesorSierra, 
            0, 
            [], 
            0
        );
        
        if ($mejorCombinacion === null) {
            return ['piezasCortadas' => [], 'piezasRestantes' => $piezasPendientes, 'desperdicio' => $retazoLargo];
        }
        
        $piezasCortadas = [];
        $piezasRestantes = [];
        
        for ($i = 0; $i < $n; $i++) {
            if (in_array($i, $mejorCombinacion['indices'])) {
                $piezasCortadas[] = $piezasPendientes[$i];
            } else {
                $piezasRestantes[] = $piezasPendientes[$i];
            }
        }
        
        return [
            'piezasCortadas' => $piezasCortadas,
            'piezasRestantes' => $piezasRestantes,
            'desperdicio' => $mejorCombinacion['desperdicio']
        ];
    }
    
    private function buscarMejorCombinacion($piezas, $espacioDisponible, $espesorSierra, $indice, $combinacionActual, $espacioUsado) {
        static $mejorSolucion = null;
        static $menorDesperdicio = PHP_INT_MAX;
        
        if ($indice == 0) {
            $mejorSolucion = null;
            $menorDesperdicio = PHP_INT_MAX;
        }
        
        $desperdicioActual = $espacioDisponible - $espacioUsado;
        
        if ($desperdicioActual >= 0 && $desperdicioActual < $menorDesperdicio) {
            $menorDesperdicio = $desperdicioActual;
            $mejorSolucion = [
                'indices' => $combinacionActual,
                'desperdicio' => $desperdicioActual
            ];
            
            if ($desperdicioActual == 0) {
                return $mejorSolucion;
            }
        }
        
        if ($indice >= count($piezas) || $desperdicioActual < 0) {
            return $mejorSolucion;
        }
        
        $piezaActual = $piezas[$indice];
        $espacioNecesario = $piezaActual['largo'] + (count($combinacionActual) > 0 ? $espesorSierra : 0);
        
        // Incluir pieza actual si cabe
        if ($espacioUsado + $espacioNecesario <= $espacioDisponible) {
            $nuevaCombinacion = $combinacionActual;
            $nuevaCombinacion[] = $indice;
            $this->buscarMejorCombinacion(
                $piezas, 
                $espacioDisponible, 
                $espesorSierra, 
                $indice + 1, 
                $nuevaCombinacion, 
                $espacioUsado + $espacioNecesario
            );
        }
        
        // No incluir pieza actual
        $this->buscarMejorCombinacion(
            $piezas, 
            $espacioDisponible, 
            $espesorSierra, 
            $indice + 1, 
            $combinacionActual, 
            $espacioUsado
        );
        
        return $mejorSolucion;
    }
    
    private function generarListaPiezas($necesidades) {
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
    
    private function generarResumen($retazosUsados, $piezasFaltantes) {
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
            'cortesPerfeitos' => count(array_filter($retazosUsados, function($r) { return $r['desperdicio'] == 0; }))
        ];
    }
    
    private function mostrarResumen($resumen) {
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
    
    private function mostrarDetalleCortes($retazosUsados) {
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
    
    private function mostrarPiezasFaltantes($piezasPendientes) {
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
    private function mostrarJson($resultado) {
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
public function generarJson($resultado) {
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