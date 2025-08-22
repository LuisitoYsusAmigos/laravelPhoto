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
use App\Services\UsoVarillasCuadro;
use App\Services\UsoLaminasCuadro;

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
     * @return float Total de cuadros procesados
     * @throws \Exception Si hay problemas con materiales o optimización
     */
    public function procesarMarcos($venta, array $cuadros)
    {
        $totalCuadros = 0;

        foreach ($cuadros as $cuadro) {
            $detalle = $this->crearDetalleVentaPersonalizada($venta, $cuadro);
            $totalCuadro = $this->procesarMaterialesCuadro($detalle, $cuadro);
            $totalCuadros += $totalCuadro;
        }

        return $totalCuadros;
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
    private function procesarMaterialesCuadro($detalle, $cuadro)
    {
        $totalCuadro = 0;

        // Procesar cada tipo de material si está especificado
        if (!empty($cuadro['id_materia_prima_varillas'])) {
            $totalCuadro += $this->procesarVarillas($detalle, $cuadro);
        }

        if (!empty($cuadro['id_materia_prima_trupans'])) {
            $totalCuadro += $this->procesarTrupans($detalle, $cuadro);
        }

        if (!empty($cuadro['id_materia_prima_vidrios'])) {
            $totalCuadro += $this->procesarVidrios($detalle, $cuadro);
        }

        if (!empty($cuadro['id_materia_prima_contornos'])) {
            $totalCuadro += $this->procesarContornos($detalle, $cuadro);
        }

        return $totalCuadro;
    }

    /**
     * Procesa varillas para un cuadro
     */
    public function procesarVarillas($detalle, $cuadro)
    {
        $varillasDisponibles = $this->obtenerVarillasDisponibles($cuadro['id_materia_prima_varillas']);
        $necesidadesCuadros = $this->crearNecesidadCuadro($cuadro);
        
        $resultado = $this->usoVarillasCuadro->optimizarCorte($necesidadesCuadros, $varillasDisponibles, 0.3);
        $jsonRespuesta = $this->usoVarillasCuadro->generarJson($resultado);

        if (!$jsonRespuesta['terminado']) {
            throw new \Exception('No se pudo optimizar el corte de varillas para el cuadro especificado');
        }

        return $this->procesarResultadoVarillas($detalle, $jsonRespuesta['retazosUsados']);
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
            throw new \Exception('No se pudo optimizar el corte de trupans para el cuadro especificado');
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
            throw new \Exception('No se pudo optimizar el corte de vidrios para el cuadro especificado');
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
            throw new \Exception('No se pudo optimizar el corte de contornos para el cuadro especificado');
        }

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
                'largo' => $cuadro['lado_a'],
                'ancho' => $cuadro['lado_b'],
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

    private function procesarResultadoVarillas($detalle, $retazosUsados)
    {
        $totalVarillas = 0;

        foreach ($retazosUsados as $retazo) {
            $precioVentaDb = DB::table('stock_varillas as sv')
                ->join('materia_prima_varillas as mpv', 'sv.id_materia_prima_varilla', '=', 'mpv.id')
                ->where('sv.id', $retazo['id'])
                ->value('mpv.precioVenta');

            $precio = $retazo['mmUsados'] * $precioVentaDb;
            $totalVarillas += $precio;

            MaterialesVentaPersonalizada::create([
                'stock_contorno_id' => null,
                'stock_trupan_id' => null,
                'stock_vidrio_id' => null,
                'stock_varilla_id' => $retazo['id'],
                'cantidad' => $retazo['cantidad'],
                'precio_unitario' => $precio,
                'detalleVP_id' => $detalle->id
            ]);
        }

        return $totalVarillas;
    }

    private function procesarResultadoLamina($detalle, $respuesta, $tipoMaterial, $cuadro)
    {
        $materialId = $respuesta['material'];
        $areaUtilizada = $cuadro['lado_a'] * $cuadro['lado_b'];

        $precioVentaDb = $this->obtenerPrecioVentaMaterial($tipoMaterial, $materialId);
        $precio = $areaUtilizada * $precioVentaDb;

        $materialData = [
            'stock_contorno_id' => $tipoMaterial === 'contorno' ? $materialId : null,
            'stock_trupan_id' => $tipoMaterial === 'trupan' ? $materialId : null,
            'stock_vidrio_id' => $tipoMaterial === 'vidrio' ? $materialId : null,
            'stock_varilla_id' => null,
            'cantidad' => 1,
            'precio_unitario' => $precio,
            'detalleVP_id' => $detalle->id
        ];

        MaterialesVentaPersonalizada::create($materialData);

        return $precio;
    }

    private function obtenerPrecioVentaMaterial($tipoMaterial, $materialId)
    {
        switch ($tipoMaterial) {
            case 'trupan':
                return DB::table('stock_trupans as st')
                    ->join('materia_prima_trupans as mpt', 'st.id_materia_prima_trupans', '=', 'mpt.id')
                    ->where('st.id', $materialId)
                    ->value('mpt.precioVenta');
            
            case 'vidrio':
                return DB::table('stock_vidrios as sv')
                    ->join('materia_prima_vidrios as mpv', 'sv.id_materia_prima_vidrio', '=', 'mpv.id')
                    ->where('sv.id', $materialId)
                    ->value('mpv.precioVenta');
            
            case 'contorno':
                return DB::table('stock_contornos as sc')
                    ->join('materia_prima_contornos as mpc', 'sc.id_materia_prima_contorno', '=', 'mpc.id')
                    ->where('sc.id', $materialId)
                    ->value('mpc.precioVenta');
            
            default:
                throw new \Exception("Tipo de material no válido: {$tipoMaterial}");
        }
    }
}