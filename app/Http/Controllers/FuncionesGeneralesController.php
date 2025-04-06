<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FuncionesGeneralesController extends Controller
{
    /**
     * Obtiene estadísticas de clientes nuevos y el porcentaje de cambio.
     */
    public function ClientesNuevos()
    {
        $resultado = DB::select("
            SELECT 
              recientes.nuevos_clientes,
              ROUND(
                IF(anteriores.clientes_anteriores = 0, 100,
                  ((recientes.nuevos_clientes - anteriores.clientes_anteriores) / anteriores.clientes_anteriores) * 100
                ), 
                2
              ) AS porcentaje_cambio
            FROM 
              (
                SELECT COUNT(*) AS nuevos_clientes
                FROM clientes
                WHERE created_at >= CURDATE() - INTERVAL 30 DAY
              ) AS recientes,
              (
                SELECT COUNT(*) AS clientes_anteriores
                FROM clientes
                WHERE created_at >= CURDATE() - INTERVAL 60 DAY
                  AND created_at < CURDATE() - INTERVAL 30 DAY
              ) AS anteriores
        ");

        return response()->json($resultado[0]);
    }
} 