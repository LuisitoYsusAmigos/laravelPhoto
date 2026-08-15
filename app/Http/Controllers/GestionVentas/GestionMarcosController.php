<?php

namespace App\Http\Controllers\GestionVentas;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use App\Models\DetalleVentaPersonalizada;
use App\Models\MaterialesVentaPersonalizada;
use App\Models\StockVarilla;
use App\Models\StockTrupan;
use App\Models\StockVidrio;
use App\Models\StockContorno;
use App\Models\MateriaPrimaVarilla;
use App\Services\UsoVarillasCuadro;
use App\Services\UsoLaminasCuadro;
use Illuminate\Support\Facades\Log;

class GestionMarcosController extends Controller
{
    protected $usoVarillasCuadro;
    protected $usoLaminasCuadro;

    public function __construct()
    {
        $this->usoVarillasCuadro = new UsoVarillasCuadro();
        $this->usoLaminasCuadro = new UsoLaminasCuadro();
    }

    /**
     * Procesa los cuadros personalizados de una venta
     * 
     * @param $venta Modelo de venta
     * @param array $cuadros Array de cuadros personalizados
     * @return array Resultados de la verificación de disponibilidad
     * @throws \Exception Si hay problemas con materiales o optimización
     */
    public function verificarDisponibilidadMarcos(array $cuadros)
    {
        // ── Paso 1: recolectar IDs únicos por tipo de material ──────────────
        $idsVarillas  = array_values(array_unique(array_filter(array_column($cuadros, 'id_materia_prima_varillas'))));
        $idsTrupans   = array_values(array_unique(array_filter(array_column($cuadros, 'id_materia_prima_trupans'))));
        $idsVidrios   = array_values(array_unique(array_filter(array_column($cuadros, 'id_materia_prima_vidrios'))));
        $idsContornos = array_values(array_unique(array_filter(array_column($cuadros, 'id_materia_prima_contornos'))));

        // ── Paso 2: 1 query batch por tipo (LEFT JOIN → existencia + stock) ─
        // Si un ID no aparece en el mapa → materia prima no encontrada.
        // Si aparece con total_stock <= 0 → sin stock disponible.
        $stockVarillas = $this->fetchStockBatch(
            'materia_prima_varillas', 'stock_varillas', 'id_materia_prima_varilla', $idsVarillas
        );
        $stockTrupans = $this->fetchStockBatch(
            'materia_prima_trupans', 'stock_trupans', 'id_materia_prima_trupans', $idsTrupans
        );
        $stockVidrios = $this->fetchStockBatch(
            'materia_prima_vidrios', 'stock_vidrios', 'id_materia_prima_vidrio', $idsVidrios
        );
        $stockContornos = $this->fetchStockBatch(
            'materia_prima_contornos', 'stock_contornos', 'id_materia_prima_contorno', $idsContornos
        );

        // ── Paso 3: mapear resultados a cada cuadro ──────────────────────────
        $resultados = [];

        foreach ($cuadros as $cuadro) {
            $errores = [];

            if (!empty($cuadro['id_materia_prima_varillas'])) {
                $id = $cuadro['id_materia_prima_varillas'];
                if (!array_key_exists($id, $stockVarillas)) {
                    $errores[] = "Varilla ID {$id} no encontrada";
                } elseif ($stockVarillas[$id] <= 0) {
                    $errores[] = "Sin stock disponible para la varilla ID {$id}";
                }
            }

            if (!empty($cuadro['id_materia_prima_trupans'])) {
                $id = $cuadro['id_materia_prima_trupans'];
                if (!array_key_exists($id, $stockTrupans)) {
                    $errores[] = "Trupan ID {$id} no encontrado";
                } elseif ($stockTrupans[$id] <= 0) {
                    $errores[] = "Sin stock disponible para el trupan ID {$id}";
                }
            }

            if (!empty($cuadro['id_materia_prima_vidrios'])) {
                $id = $cuadro['id_materia_prima_vidrios'];
                if (!array_key_exists($id, $stockVidrios)) {
                    $errores[] = "Vidrio ID {$id} no encontrado";
                } elseif ($stockVidrios[$id] <= 0) {
                    $errores[] = "Sin stock disponible para el vidrio ID {$id}";
                }
            }

            if (!empty($cuadro['id_materia_prima_contornos'])) {
                $id = $cuadro['id_materia_prima_contornos'];
                if (!array_key_exists($id, $stockContornos)) {
                    $errores[] = "Contorno ID {$id} no encontrado";
                } elseif ($stockContornos[$id] <= 0) {
                    $errores[] = "Sin stock disponible para el contorno ID {$id}";
                }
            }

            $resultados[] = [
                'cuadro' => $cuadro,
                'valido' => empty($errores),
                'errores' => $errores,
            ];
        }

        return $resultados;
    }

    /**
     * Hace una sola query que verifica existencia y suma stock en batch.
     * Retorna un mapa [ id_materia_prima => total_stock ].
     * Si un ID no aparece en el mapa, significa que no existe en la tabla de materia prima.
     *
     * @param  string  $tablaMp     Tabla de materia prima (ej. 'materia_prima_varillas')
     * @param  string  $tablaStock  Tabla de stock (ej. 'stock_varillas')
     * @param  string  $fkColumn    Columna FK en tablaStock (ej. 'id_materia_prima_varilla')
     * @param  array   $ids         IDs únicos a consultar
     * @return array<int, int>      [ id => total_stock ]
     */
    private function fetchStockBatch(string $tablaMp, string $tablaStock, string $fkColumn, array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        $rows = DB::table("{$tablaMp} as mp")
            ->leftJoin("{$tablaStock} as s", "s.{$fkColumn}", '=', 'mp.id')
            ->whereIn('mp.id', $ids)
            ->groupBy('mp.id')
            ->selectRaw('mp.id, COALESCE(SUM(CASE WHEN s.stock > 0 THEN s.stock ELSE 0 END), 0) as total_stock')
            ->get();

        $mapa = [];
        foreach ($rows as $row) {
            $mapa[(int) $row->id] = (int) $row->total_stock;
        }

        return $mapa;
    }

    public function procesarMarcos($venta, array $cuadros, $factorPrecioVenta)
    {
        $totalCuadros = 0;
        $medidasExternas = [];

        foreach ($cuadros as $index => $cuadro) {

            $detalle = $this->crearDetalleVentaPersonalizada($venta, $cuadro);
            $resultadoMateriales = $this->procesarMaterialesCuadro($detalle, $cuadro, $factorPrecioVenta);
            $totalCuadros += $resultadoMateriales['total'];

            // Guardar las medidas externas asociadas al detalle (sin persistir en BD)
            if (isset($cuadro['lado_a_ext'], $cuadro['lado_b_ext'])) {
                $medidasExternas[$detalle->id] = [
                    'lado_a_ext' => $cuadro['lado_a_ext'],
                    'lado_b_ext' => $cuadro['lado_b_ext'],
                ];
            }
        }

        return [
            'total'          => $totalCuadros,
            'medidas_externas' => $medidasExternas,
        ];
    }

    /**
     * Crea el detalle de venta personalizada
     */
    private function crearDetalleVentaPersonalizada($venta, $cuadro)
    {
        return DetalleVentaPersonalizada::create([
            'lado_a' => $cuadro['lado_a'],
            'lado_b' => $cuadro['lado_b'],
            'id_venta' => $venta->id,
            'id_materia_prima_varillas' => $cuadro['id_materia_prima_varillas'] ?? null,
            'id_materia_prima_trupans' => $cuadro['id_materia_prima_trupans'] ?? null,
            'id_materia_prima_vidrios' => $cuadro['id_materia_prima_vidrios'] ?? null,
            'id_materia_prima_contornos' => $cuadro['id_materia_prima_contornos'] ?? null,
        ]);
    }

    /**
     * Procesa todos los materiales de un cuadro
     */
    private function procesarMaterialesCuadro($detalle, $cuadro, $factorPrecioVenta)
    {
        $precioVarillas = 0;
        $precioTrupans = 0;
        $precioVidrios = 0;
        $precioContornos = 0;
        $tiempos = [];

        if (!empty($cuadro['id_materia_prima_varillas'])) {
            $t0 = microtime(true);
            $precioVarillas = $this->procesarVarillas($detalle, $cuadro);
            $tiempos['procesarVarillas'] = round((microtime(true) - $t0) * 1000, 2) . ' ms';
        }

        if (!empty($cuadro['id_materia_prima_trupans'])) {
            $t0 = microtime(true);
            $precioTrupans = $this->procesarTrupans($detalle, $cuadro);
            $tiempos['procesarTrupans'] = round((microtime(true) - $t0) * 1000, 2) . ' ms';
        }

        if (!empty($cuadro['id_materia_prima_vidrios'])) {
            $t0 = microtime(true);
            $precioVidrios = $this->procesarVidrios($detalle, $cuadro);
            $tiempos['procesarVidrios'] = round((microtime(true) - $t0) * 1000, 2) . ' ms';
        }

        if (!empty($cuadro['id_materia_prima_contornos'])) {
            $t0 = microtime(true);
            $precioContornos = $this->procesarContornos($detalle, $cuadro);
            $tiempos['procesarContornos'] = round((microtime(true) - $t0) * 1000, 2) . ' ms';
        }

        return [
            'total' => intval(($precioVarillas + $precioTrupans + $precioVidrios + $precioContornos) * $factorPrecioVenta),
        ];
    }

    /**
     * Procesa varillas para un cuadro
     */
    public function procesarVarillas($detalle, $cuadro)
    {
        $varillasDisponibles = $this->obtenerVarillasDisponibles($cuadro['id_materia_prima_varillas']);
        $necesidadesCuadros = $this->crearNecesidadCuadro($cuadro);

        $resultado = $this->usoVarillasCuadro->optimizarCorte($necesidadesCuadros, $varillasDisponibles, 0.0);
        // dd removido
        $jsonRespuesta = $this->usoVarillasCuadro->generarJson($resultado);
        //dd($varillasDisponibles, $necesidadesCuadros, $resultado, $jsonRespuesta);
        if (!$jsonRespuesta['terminado']) {
            throw new \Exception('No hay disponibilidad de esas medidas de varillas para el cuadro especificado');
        }

        return $this->procesarResultadoVarillas($detalle, $jsonRespuesta['retazosUsados'], $resultado['retazosUsados']);
    }

    /**
     * Procesa trupans para un cuadro
     */
    public function procesarTrupans($detalle, $cuadro)
    {
        $trupansDisponibles = $this->obtenerTrupansDisponibles($cuadro['id_materia_prima_trupans']);
        $necesidadCuadro = $this->crearNecesidadCuadroLamina($cuadro);

        $respuesta = $this->usoLaminasCuadro->optimizarCuadro($necesidadCuadro, $trupansDisponibles);

        if (!$respuesta['terminado']) {
            throw new \Exception('No hay disponibilidad de esas medidas de trupan para el cuadro especificado');
        }

        return $this->procesarResultadoLamina($detalle, $respuesta, 'trupan', $cuadro);
    }

    /**
     * Procesa vidrios para un cuadro
     */
    public function procesarVidrios($detalle, $cuadro)
    {
        $vidriosDisponibles = $this->obtenerVidriosDisponibles($cuadro['id_materia_prima_vidrios']);
        $necesidadCuadro = $this->crearNecesidadCuadroLamina($cuadro);

        $respuesta = $this->usoLaminasCuadro->optimizarCuadro($necesidadCuadro, $vidriosDisponibles);


        if (!$respuesta['terminado']) {
            throw new \Exception('No hay disponibilidad de esas medidas de vidrios para el cuadro especificado');
        }

        return $this->procesarResultadoLamina($detalle, $respuesta, 'vidrio', $cuadro);
    }

    /**
     * Procesa contornos para un cuadro
     */
    public function procesarContornos($detalle, $cuadro)
    {
        $contornosDisponibles = $this->obtenerContornosDisponibles($cuadro['id_materia_prima_contornos']);
        $necesidadCuadro = $this->crearNecesidadCuadroLamina($cuadro);

        $respuesta = $this->usoLaminasCuadro->optimizarCuadro($necesidadCuadro, $contornosDisponibles);

        if (!$respuesta['terminado']) {
            throw new \Exception('No hay disponibilidad de esas medidas de contornos para el cuadro especificado');
        }
        //dd($respuesta, $detalle, 'contorno', $cuadro);
        return $this->procesarResultadoLamina($detalle, $respuesta, 'contorno', $cuadro);
    }

    // Métodos auxiliares para obtener materiales disponibles

    private function obtenerVarillasDisponibles($idMateriaPrima)
    {
        return StockVarilla::where('id_materia_prima_varilla', $idMateriaPrima)
            ->where('stock', '>', 0)
            ->get()
            ->map(function ($item) {
                return [$item->id, $item->largo, $item->stock];
            })
            ->values()
            ->toArray();
    }

    private function obtenerTrupansDisponibles($idMateriaPrima)
    {
        return StockTrupan::where('id_materia_prima_trupans', $idMateriaPrima)
            ->where('stock', '>', 0)
            ->get()
            ->map(function ($item) {
                return [$item->id, $item->alto, $item->largo, $item->stock];
            })
            ->values()
            ->toArray();
    }

    private function obtenerVidriosDisponibles($idMateriaPrima)
    {
        return StockVidrio::where('id_materia_prima_vidrio', $idMateriaPrima)
            ->where('stock', '>', 0)
            ->get()
            ->map(function ($item) {
                return [$item->id, $item->alto, $item->largo, $item->stock];
            })
            ->values()
            ->toArray();
    }

    private function obtenerContornosDisponibles($idMateriaPrima)
    {
        return StockContorno::where('id_materia_prima_contorno', $idMateriaPrima)
            ->where('stock', '>', 0)
            ->get()
            ->map(function ($item) {
                return [$item->id, $item->alto, $item->largo, $item->stock];
            })
            ->values()
            ->toArray();
    }

    // Métodos auxiliares para crear estructuras de necesidades

    private function crearNecesidadCuadro($cuadro)
    {
        return [
            [
                'largo' => $cuadro['lado_a_ext'],
                'ancho' => $cuadro['lado_b_ext'],
                'cantidad' => $cuadro['cantidad'],
                'nombre' => 'Cuadro'
            ]
        ];
    }

    private function crearNecesidadCuadroLamina($cuadro)
    {
        return [
            'largo' => $cuadro['lado_a'],
            'ancho' => $cuadro['lado_b'],
            'cantidad' => $cuadro['cantidad'],
            'nombre' => 'Cuadro'
        ];
    }

    // Métodos para procesar resultados

    private function procesarResultadoVarillas($detalle, $retazosUsados, $cortesDetallados = [])
    {
        $totalVarillas = 0;

        foreach ($retazosUsados as $retazo) {
            // Obtener datos de la materia prima varilla
            $datosVarilla = DB::table('stock_varillas as sv')
                ->join('materia_prima_varillas as mpv', 'sv.id_materia_prima_varilla', '=', 'mpv.id')
                ->where('sv.id', $retazo['id'])
                ->first(['mpv.precioVenta', 'mpv.factor_desperdicio']);

            if (!$datosVarilla) {
                throw new \Exception("Datos de varilla no encontrados para stock ID: {$retazo['id']}");
            }

            // Convertir mm usados a metros
            $metrosUsados = $retazo['mmUsados'] / 1000;


            // Calcular precio total aplicando la división por 10 usada históricamente para las varillas
            $precioTotal = ($metrosUsados * $datosVarilla->precioVenta * $datosVarilla->factor_desperdicio) / 10;
            $precioUnitario = $retazo['cantidad'] > 0 ? ($precioTotal / $retazo['cantidad']) : $precioTotal;

            $totalVarillas += $precioTotal;

            $materialVenta = MaterialesVentaPersonalizada::create([
                'stock_contorno_id' => null,
                'stock_trupan_id' => null,
                'stock_vidrio_id' => null,
                'stock_varilla_id' => $retazo['id'],
                'cantidad' => $retazo['cantidad'],
                'precio_unitario' => intval($precioUnitario),
                'detalleVP_id' => $detalle->id
            ]);

            // Registrar los cortes detallados en batch (1 INSERT con N filas)
            if (!empty($cortesDetallados)) {
                $cortesBatch = [];
                $now = now();
                foreach ($cortesDetallados as $corteUnico) {
                    if ($corteUnico['idOriginal'] == $retazo['id']) {
                        foreach ($corteUnico['piezas'] as $pieza) {
                            $cortesBatch[] = [
                                'material_vp_id'  => $materialVenta->id,
                                'stock_varilla_id' => $retazo['id'],
                                'largo_corte'      => $pieza['largo'],
                                'ancho_corte'      => null,
                                'tipo_corte'       => $pieza['tipo'],
                                'origen'           => $pieza['origen'],
                                'created_at'       => $now,
                                'updated_at'       => $now,
                            ];
                        }
                    }
                }
                if (!empty($cortesBatch)) {
                    \App\Models\CorteMaterialVenta::insert($cortesBatch);
                }
            }

            // Restar stock físico en la base de datos
            StockVarilla::where('id', $retazo['id'])->decrement('stock', $retazo['cantidad']);
        }

        return $totalVarillas;
    }

    private function procesarResultadoLamina($detalle, $respuesta, $tipoMaterial, $cuadro)
    {
        $materialId = $respuesta['material'];

        // Área del cuadro en m² (convervión milímetros a metros)
        $areaM2 = ($cuadro['lado_a'] / 1000) * ($cuadro['lado_b'] / 1000);

        // Obtener precio por m² y factor de desperdicio de la materia prima
        $datosMateriaPrima = $this->obtenerDatosMateriaPrima($tipoMaterial, $cuadro);
        $precioM2 = $datosMateriaPrima['precio_m2'];
        $factorDesperdicio = $datosMateriaPrima['factor_desperdicio'];

        // Aplicar fórmula correcta: área_m² × precio_m² × factor_desperdicio (por unidad)
        $precioUnitario = $areaM2 * $precioM2 * $factorDesperdicio;
        $precioTotal = intval($precioUnitario * $cuadro['cantidad']);

        $materialData = [
            'stock_contorno_id' => $tipoMaterial === 'contorno' ? $materialId : null,
            'stock_trupan_id' => $tipoMaterial === 'trupan' ? $materialId : null,
            'stock_vidrio_id' => $tipoMaterial === 'vidrio' ? $materialId : null,
            'stock_varilla_id' => null,
            'cantidad' => $cuadro['cantidad'],
            'precio_unitario' => intval($precioUnitario),
            'detalleVP_id' => $detalle->id
        ];

        $materialVenta = MaterialesVentaPersonalizada::create($materialData);

        // Registrar los cortes en batch (1 INSERT con N filas en lugar de N INSERTs)
        $cortesBatch = [];
        $now = now();
        for ($i = 0; $i < $cuadro['cantidad']; $i++) {
            $cortesBatch[] = [
                'material_vp_id'    => $materialVenta->id,
                'stock_varilla_id'  => null,
                'stock_trupan_id'   => $materialData['stock_trupan_id'],
                'stock_vidrio_id'   => $materialData['stock_vidrio_id'],
                'stock_contorno_id' => $materialData['stock_contorno_id'],
                'largo_corte'       => $cuadro['lado_a'],
                'ancho_corte'       => $cuadro['lado_b'],
                'tipo_corte'        => 'lámina',
                'origen'            => 'Cuadro #' . ($i + 1),
                'created_at'        => $now,
                'updated_at'        => $now,
            ];
        }
        \App\Models\CorteMaterialVenta::insert($cortesBatch);
        // restar stock vidrios
        // generar un if triple para los 3 casos de contorno trupan vidrio
        if ($tipoMaterial === 'contorno') {
            StockContorno::where('id', $materialData['stock_contorno_id'])->decrement('stock', $materialData['cantidad']);
        } elseif ($tipoMaterial === 'trupan') {
            StockTrupan::where('id', $materialData['stock_trupan_id'])->decrement('stock', $materialData['cantidad']);
        } elseif ($tipoMaterial === 'vidrio') {
            StockVidrio::where('id', $materialData['stock_vidrio_id'])->decrement('stock', $materialData['cantidad']);
        }

        return $precioTotal;
    }

    /**
     * Obtiene precio_m2 y factor_desperdicio de la materia prima específica
     */
    private function obtenerDatosMateriaPrima($tipoMaterial, $cuadro)
    {
        switch ($tipoMaterial) {
            case 'trupan':
                $materiaPrima = DB::table('materia_prima_trupans')
                    ->where('id', $cuadro['id_materia_prima_trupans'])
                    ->first(['precioVenta', 'largo', 'alto', 'factor_desperdicio']);
                break;

            case 'vidrio':
                $materiaPrima = DB::table('materia_prima_vidrios')
                    ->where('id', $cuadro['id_materia_prima_vidrios'])
                    ->first(['precioVenta', 'largo', 'alto', 'factor_desperdicio']);
                break;

            case 'contorno':
                $materiaPrima = DB::table('materia_prima_contornos')
                    ->where('id', $cuadro['id_materia_prima_contornos'])
                    ->first(['precioVenta', 'largo', 'alto', 'factor_desperdicio']);
                break;

            default:
                throw new \Exception("Tipo de material no válido: {$tipoMaterial}");
        }

        if (!$materiaPrima) {
            throw new \Exception("Materia prima no encontrada para tipo: {$tipoMaterial}");
        }

        // Calcular precio_m2 como lo hacen los modelos
        $area_mm2 = $materiaPrima->alto * $materiaPrima->largo;
        $area_m2 = $area_mm2 / 1_000_000;
        //$precio_m2 = round($materiaPrima->precioVenta / $area_m2);
        $precio_m2 = $materiaPrima->precioVenta;

        return [
            'precio_m2' => $precio_m2,
            'factor_desperdicio' => $materiaPrima->factor_desperdicio
        ];
    }




    public function simularPrecioMarco(array $cuadros, $factorPrecioVenta)
    {
        $totalCuadros = 0;
        foreach ($cuadros as $index => $cuadro) {
            $resultadoMateriales = $this->simularMaterialesCuadro($cuadro, $factorPrecioVenta);
            $totalCuadros += $resultadoMateriales['total'];
        }
        return [
            'total' => $totalCuadros,
        ];
    }

    private function simularMaterialesCuadro($cuadro, $factorPrecioVenta)
    {
        $precioVarillas = 0;
        $precioTrupans = 0;
        $precioVidrios = 0;
        $precioContornos = 0;

        if (!empty($cuadro['id_materia_prima_varillas'])) {
            $precioVarillas = $this->simularVarillas($cuadro);
        }
        if (!empty($cuadro['id_materia_prima_trupans'])) {
            $precioTrupans = $this->simularTrupans($cuadro);
        }
        if (!empty($cuadro['id_materia_prima_vidrios'])) {
            $precioVidrios = $this->simularVidrios($cuadro);
        }
        if (!empty($cuadro['id_materia_prima_contornos'])) {
            $precioContornos = $this->simularContornos($cuadro);
        }

        return [
            'total' => intval(($precioVarillas + $precioTrupans + $precioVidrios + $precioContornos) * $factorPrecioVenta),
        ];
    }

    public function simularVarillas($cuadro)
    {
        $varillasDisponibles = $this->obtenerVarillasDisponibles($cuadro['id_materia_prima_varillas']);
        $necesidadesCuadros = $this->crearNecesidadCuadro($cuadro);

        $resultado = $this->usoVarillasCuadro->optimizarCorte($necesidadesCuadros, $varillasDisponibles, 0.3);
        $jsonRespuesta = $this->usoVarillasCuadro->generarJson($resultado);

        if (!$jsonRespuesta['terminado']) {
            throw new \Exception('No se pudo obtener las medidas de varillas para el cuadro especificado');
        }

        return $this->simularResultadoVarillas($jsonRespuesta['retazosUsados']);
    }

    public function simularTrupans($cuadro)
    {
        $trupansDisponibles = $this->obtenerTrupansDisponibles($cuadro['id_materia_prima_trupans']);
        $necesidadCuadro = $this->crearNecesidadCuadroLamina($cuadro);

        $respuesta = $this->usoLaminasCuadro->optimizarCuadro($necesidadCuadro, $trupansDisponibles);

        if (!$respuesta['terminado']) {
            throw new \Exception('No se pudo optimizar el corte de trupans para el cuadro especificado');
        }

        return $this->simularResultadoLamina($respuesta, 'trupan', $cuadro);
    }

    public function simularVidrios($cuadro)
    {
        $vidriosDisponibles = $this->obtenerVidriosDisponibles($cuadro['id_materia_prima_vidrios']);
        $necesidadCuadro = $this->crearNecesidadCuadroLamina($cuadro);

        $respuesta = $this->usoLaminasCuadro->optimizarCuadro($necesidadCuadro, $vidriosDisponibles);

        if (!$respuesta['terminado']) {
            throw new \Exception('No se pudo optimizar el corte de vidrios para el cuadro especificado');
        }

        return $this->simularResultadoLamina($respuesta, 'vidrio', $cuadro);
    }

    public function simularContornos($cuadro)
    {
        $contornosDisponibles = $this->obtenerContornosDisponibles($cuadro['id_materia_prima_contornos']);
        $necesidadCuadro = $this->crearNecesidadCuadroLamina($cuadro);

        $respuesta = $this->usoLaminasCuadro->optimizarCuadro($necesidadCuadro, $contornosDisponibles);

        if (!$respuesta['terminado']) {
            throw new \Exception('No se pudo optimizar el corte de contornos para el cuadro especificado');
        }
        return $this->simularResultadoLamina($respuesta, 'contorno', $cuadro);
    }

    private function simularResultadoVarillas($retazosUsados)
    {
        $totalVarillas = 0;

        foreach ($retazosUsados as $retazo) {
            $datosVarilla = DB::table('stock_varillas as sv')
                ->join('materia_prima_varillas as mpv', 'sv.id_materia_prima_varilla', '=', 'mpv.id')
                ->where('sv.id', $retazo['id'])
                ->first(['mpv.precioVenta', 'mpv.factor_desperdicio']);

            if (!$datosVarilla) {
                throw new \Exception("Datos de varilla no encontrados para stock ID: {$retazo['id']}");
            }

            $metrosUsados = $retazo['mmUsados'] / 1000;
            $precioTotal = ($metrosUsados * $datosVarilla->precioVenta * $datosVarilla->factor_desperdicio) / 10;
            $totalVarillas += intval($precioTotal);
        }

        return $totalVarillas;
    }

    private function simularResultadoLamina($respuesta, $tipoMaterial, $cuadro)
    {
        // Área del cuadro en m² directo en base a los milímetros (mm a metros)
        $areaM2 = ($cuadro['lado_a'] / 1000) * ($cuadro['lado_b'] / 1000);

        $datosMateriaPrima = $this->obtenerDatosMateriaPrima($tipoMaterial, $cuadro);
        $precioM2 = $datosMateriaPrima['precio_m2'];
        $factorDesperdicio = $datosMateriaPrima['factor_desperdicio'];

        $precioTotal = intval($areaM2 * $precioM2 * $factorDesperdicio * $cuadro['cantidad']);

        return $precioTotal;
    }

    public function obtenerMarcoExterno($cuadro)
    {
        $varilla = MateriaPrimaVarilla::find($cuadro['id_materia_prima_varillas']);
        // define una varible grosor con el grosor de este
        $grosor = $varilla->grosor;
        //dd($grosor);
        $resultado = [
            'lado_a' => $cuadro['lado_a'] + 2 * $grosor,
            'lado_b' => $cuadro['lado_b'] + 2 * $grosor,
        ];
        /*
                dd([
                    'lado_a_cuadro' => $cuadros[0]['lado_a'],
                    'lado_b_cuadro' => $cuadros[0]['lado_b'],
                    'grosor' => $grosor,
                    'resultado' => $resultado
                ]);
                */
        return $resultado;


    }
}