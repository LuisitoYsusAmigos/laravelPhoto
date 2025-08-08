<?php

namespace App\Services;

class UsoLaminasCuadro {
    

    public function optimizarCuadro($necesidadCuadro, $materialesLamina) {
        $largoCuadro = $necesidadCuadro['largo'];
        $anchoCuadro = $necesidadCuadro['ancho'];
        
        $laminasCompatibles = [];
        
        // Buscar láminas que puedan contener el cuadro
        foreach ($materialesLamina as $lamina) {
            $idLamina = $lamina[0];
            $largoLamina = $lamina[1];
            $anchoLamina = $lamina[2];
            $cantidadDisponible = $lamina[3];
            
            // Verificar si el cuadro cabe en la lámina (en cualquier orientación)
            if ($cantidadDisponible > 0 && $this->cuadroCabeEnLamina($largoCuadro, $anchoCuadro, $largoLamina, $anchoLamina)) {
                
                // Calcular desperdicio en ambas orientaciones
                $desperdicio1 = $this->calcularDesperdicio($largoCuadro, $anchoCuadro, $largoLamina, $anchoLamina);
                $desperdicio2 = $this->calcularDesperdicio($anchoCuadro, $largoCuadro, $largoLamina, $anchoLamina);
                
                // Usar la orientación con menor desperdicio
                $desperdicioMinimo = min($desperdicio1, $desperdicio2);
                
                $laminasCompatibles[] = [
                    'id' => $idLamina,
                    'largo' => $largoLamina,
                    'ancho' => $anchoLamina,
                    'cantidad' => $cantidadDisponible,
                    'desperdicio' => $desperdicioMinimo,
                    'areaLamina' => $largoLamina * $anchoLamina,
                    'areaCuadro' => $largoCuadro * $anchoCuadro,
                    'eficiencia' => (($largoCuadro * $anchoCuadro) / ($largoLamina * $anchoLamina)) * 100
                ];
            }
        }
        
        // Si no hay láminas compatibles
        if (empty($laminasCompatibles)) {
            return [
                'terminado' => false,
                'material' => null
            ];
        }
        
        // Encontrar la lámina con menor desperdicio
        $mejorLamina = $this->encontrarMejorLamina($laminasCompatibles);
        
        return [
            'terminado' => true,
            'material' => $mejorLamina['id']
        ];
    }
    
   
    private function cuadroCabeEnLamina($largoCuadro, $anchoCuadro, $largoLamina, $anchoLamina) {
        // Orientación 1: cuadro como está
        $cabe1 = ($largoCuadro <= $largoLamina && $anchoCuadro <= $anchoLamina);
        
        // Orientación 2: cuadro rotado 90°
        $cabe2 = ($anchoCuadro <= $largoLamina && $largoCuadro <= $anchoLamina);
        
        return $cabe1 || $cabe2;
    }
    
    /**
     * Calcula el desperdicio en cm² para una orientación específica
     */
    private function calcularDesperdicio($largoCuadro, $anchoCuadro, $largoLamina, $anchoLamina) {
        // Solo calcular si el cuadro cabe en esta orientación
        if ($largoCuadro <= $largoLamina && $anchoCuadro <= $anchoLamina) {
            $areaLamina = $largoLamina * $anchoLamina;
            $areaCuadro = $largoCuadro * $anchoCuadro;
            return $areaLamina - $areaCuadro;
        }
        
        return PHP_INT_MAX; // No cabe en esta orientación
    }
    
    /**
     * Encuentra la lámina con menor desperdicio
     */
    private function encontrarMejorLamina($laminasCompatibles) {
        $mejorLamina = null;
        $menorDesperdicio = PHP_INT_MAX;
        
        foreach ($laminasCompatibles as $lamina) {
            if ($lamina['desperdicio'] < $menorDesperdicio) {
                $menorDesperdicio = $lamina['desperdicio'];
                $mejorLamina = $lamina;
            } elseif ($lamina['desperdicio'] == $menorDesperdicio) {
                // En caso de empate, preferir la lámina más pequeña (menos área total)
                if ($mejorLamina === null || $lamina['areaLamina'] < $mejorLamina['areaLamina']) {
                    $mejorLamina = $lamina;
                }
            }
        }
        
        return $mejorLamina;
    }
}

?>