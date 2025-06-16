<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MateriaPrimaTrupan extends Model
{
    use HasFactory;

    protected $fillable = [
        'descripcion',
        'precioCompra',
        'precioVenta',
        'largo',
        'alto',
        'grosor',
        'factor_desperdicio',
        'categoria_id',
        'sub_categoria_id',
        'stock_global_actual',
        'stock_global_minimo',
        'id_sucursal',
        'imagen'
    ];

    protected $appends = ['precio_m2', 'imagen_url'];

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function subCategoria()
    {
        return $this->belongsTo(SubCategoria::class);
    }

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }

    public function getImagenUrlAttribute()
    {
        return $this->imagen ? asset('storage/materias_primas_trupan/' . basename($this->imagen)) : null;
    }

    // Accessor para calcular el precio por metro cuadrado (en centavos)
    protected function precioM2(): Attribute
    {
        return Attribute::get(function () {
            if ($this->largo > 0 && $this->alto > 0) {
                $area_m2 = ($this->largo * $this->alto) / 1_000_000;
                return round($this->precioVenta / $area_m2);
            }
            return null;
        });
    }
}
