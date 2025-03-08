<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    protected $table = 'productos';

    protected $fillable = [
        'descripcion',
        'precioCompra',
        'precioVenta',
        'stock',
        'stockMin',
        'actualizacion',
        'sucursal_id',
        'categoria_id',
        'sub_categoria_id'
    ];

    // Relaciones con otras tablas
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function subCategoria()
    {
        return $this->belongsTo(SubCategoria::class);
    }
}
