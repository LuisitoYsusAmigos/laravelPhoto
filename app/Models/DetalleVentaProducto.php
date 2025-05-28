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
        'idProducto'
    ];

    // Relación con Venta
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'idVenta');
    }

    // Relación con Producto
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'idProducto');
    }
}
