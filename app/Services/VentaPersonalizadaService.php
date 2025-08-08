<?php

namespace App\Services;

use App\Models\MateriaPrimaVarilla;
use App\Models\MateriaPrimaTrupan;
use App\Models\MateriaPrimaVidrio;
use App\Models\MateriaPrimaContorno;

class VentaPersonalizadaService
{
    /**
     * Validar disponibilidad de varillas
     */
    public function validarDisponibilidadVarilla($idVarilla, $cantidadRequerida)
    {
        if (!$idVarilla) return true; // Si es null, no validar
        
        $varilla = MateriaPrimaVarilla::find($idVarilla);
        
        if (!$varilla) {
            return ['valid' => false, 'message' => 'Varilla no encontrada'];
        }
        
        if ($varilla->stock_global_actual < $cantidadRequerida) {
            return [
                'valid' => false, 
                'message' => "Stock insuficiente de varillas. Disponible: {$varilla->stock_global_actual}, Requerido: {$cantidadRequerida}"
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validar disponibilidad de trupans
     */
    public function validarDisponibilidadTrupan($idTrupan, $metrosCuadrados)
    {
        if (!$idTrupan) return true; // Si es null, no validar
        
        $trupan = MateriaPrimaTrupan::find($idTrupan);
        
        if (!$trupan) {
            return ['valid' => false, 'message' => 'Trupan no encontrado'];
        }
        
        if ($trupan->stock_global_actual < $metrosCuadrados) {
            return [
                'valid' => false,
                'message' => "Stock insuficiente de trupan. Disponible: {$trupan->stock_global_actual}m², Requerido: {$metrosCuadrados}m²"
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validar disponibilidad de vidrios
     */
    public function validarDisponibilidadVidrio($idVidrio, $metrosCuadrados)
    {
        if (!$idVidrio) return true;
        
        $vidrio = MateriaPrimaVidrio::find($idVidrio);
        
        if (!$vidrio) {
            return ['valid' => false, 'message' => 'Vidrio no encontrado'];
        }
        
        if ($vidrio->stock_global_actual < $metrosCuadrados) {
            return [
                'valid' => false,
                'message' => "Stock insuficiente de vidrio. Disponible: {$vidrio->stock_global_actual}m², Requerido: {$metrosCuadrados}m²"
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validar disponibilidad de contornos
     */
    public function validarDisponibilidadContorno($idContorno, $longitudTotal)
    {
        if (!$idContorno) return true;
        
        $contorno = MateriaPrimaContorno::find($idContorno);
        
        if (!$contorno) {
            return ['valid' => false, 'message' => 'Contorno no encontrado'];
        }
        
        if ($contorno->stock_global_actual < $longitudTotal) {
            return [
                'valid' => false,
                'message' => "Stock insuficiente de contorno. Disponible: {$contorno->stock_global_actual}mm, Requerido: {$longitudTotal}mm"
            ];
        }
        
        return ['valid' => true];
    }
    
    /**
     * Validar todas las disponibilidades
     */
    public function validarTodasDisponibilidades($datos)
    {
        $ladoA = $datos['lado_a'];
        $ladoB = $datos['lado_b'];
        
        // Cálculos
        $longitudTotal = (2 * $ladoA) + (2 * $ladoB);
        $metrosCuadrados = ($ladoA * $ladoB) / 1000000;
        
        // Validaciones
        $validaciones = [
            $this->validarDisponibilidadVarilla($datos['id_materia_prima_varillas'] ?? null, $longitudTotal),
            $this->validarDisponibilidadTrupan($datos['id_materia_prima_trupans'] ?? null, $metrosCuadrados),
            $this->validarDisponibilidadVidrio($datos['id_materia_prima_vidrios'] ?? null, $metrosCuadrados),
            $this->validarDisponibilidadContorno($datos['id_materia_prima_contornos'] ?? null, $longitudTotal)
        ];
        
        foreach ($validaciones as $validacion) {
            if (is_array($validacion) && !$validacion['valid']) {
                return $validacion;
            }
        }
        
        return ['valid' => true];
    }
}