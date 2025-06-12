<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleVentaProducto extends Model
{
    use HasFactory;

    protected $table = 'detalle_venta_productos';

    protected $fillable = [
        'cantidad',
        'precio',
        'idVenta',
        'idProducto',
        'id_stock_producto', // Nuevo campo para rastrear lote
    ];

    // Relación con la venta
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'idVenta');
    }

    // Relación con el producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'idProducto');
    }

    // Relación con el lote (stock_producto)
    public function stockProducto()
    {
        return $this->belongsTo(StockProducto::class, 'id_stock_producto');
    }
}
