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
        'stock_global_actual',
        'stock_global_minimo',
        'actualizacion',
        'id_sucursal',
        'categoria_id',
        'sub_categoria_id',
        'imagen'
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
    public function getImagenUrlAttribute()
    {
        return $this->imagen ? asset('storage/' . $this->imagen) : null;
    }
}
