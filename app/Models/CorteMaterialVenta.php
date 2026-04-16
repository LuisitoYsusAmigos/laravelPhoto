<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorteMaterialVenta extends Model
{
    use HasFactory;

    protected $table = 'corte_material_ventas';

    protected $fillable = [
        'material_vp_id',
        'stock_varilla_id',
        'stock_trupan_id',
        'stock_vidrio_id',
        'stock_contorno_id',
        'largo_corte',
        'ancho_corte',
        'tipo_corte',
        'origen'
    ];

    public function materialVentaPersonalizada()
    {
        return $this->belongsTo(MaterialesVentaPersonalizada::class, 'material_vp_id');
    }

    public function stockVarilla()
    {
        return $this->belongsTo(StockVarilla::class, 'stock_varilla_id');
    }

    public function stockTrupan()
    {
        return $this->belongsTo(StockTrupan::class, 'stock_trupan_id');
    }

    public function stockVidrio()
    {
        return $this->belongsTo(StockVidrio::class, 'stock_vidrio_id');
    }

    public function stockContorno()
    {
        return $this->belongsTo(StockContorno::class, 'stock_contorno_id');
    }

}
