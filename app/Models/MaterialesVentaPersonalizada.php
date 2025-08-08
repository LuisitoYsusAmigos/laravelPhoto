<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaterialesVentaPersonalizada extends Model
{
    use HasFactory;

    protected $table = 'materiales_venta_personalizadas';

    protected $fillable = [
        'stock_contorno_id',
        'stock_trupan_id', 
        'stock_vidrio_id',
        'stock_varilla_id',
        'cantidad',
        'precio_unitario',  
        'detalleVP_id'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'integer',
    ];

    // Relaciones
    public function stockContorno()
    {
        return $this->belongsTo(StockContorno::class, 'stock_contorno_id');
    }

    public function stockTrupan()
    {
        return $this->belongsTo(StockTrupan::class, 'stock_trupan_id');
    }

    public function stockVidrio()
    {
        return $this->belongsTo(StockVidrio::class, 'stock_vidrio_id');
    }

    public function stockVarilla()
    {
        return $this->belongsTo(StockVarilla::class, 'stock_varilla_id');
    }
    public function detalleVentaPersonalizada()
    {
        return $this->belongsTo(DetalleVentaPersonalizada::class, 'detalleVP_id');
    }

    // Método para obtener el material activo (el que no es null)
    public function getMaterialActivoAttribute()
    {
        if ($this->stock_contorno_id) return $this->stockContorno;
        if ($this->stock_trupan_id) return $this->stockTrupan;
        if ($this->stock_vidrio_id) return $this->stockVidrio;
        if ($this->stock_varilla_id) return $this->stockVarilla;
        
        return null;
    }

    // Método para obtener el tipo de material
    public function getTipoMaterialAttribute()
    {
        if ($this->stock_contorno_id) return 'contorno';
        if ($this->stock_trupan_id) return 'trupan';
        if ($this->stock_vidrio_id) return 'vidrio';
        if ($this->stock_varilla_id) return 'varilla';
        
        return null;
    }
}