<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockProducto extends Model
{
    
    protected $table = 'stock_productos';

    protected $fillable = [
            'stock',
            'precio',
            'contable',
            'id_producto',
    ];
        public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}
