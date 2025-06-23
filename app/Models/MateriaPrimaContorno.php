<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MateriaPrimaContorno extends Model
{
    protected $table = 'materia_prima_contornos';

    protected $fillable = [
        'codigo',
        'descripcion',
        'precioCompra',
        'precioVenta',
        'largo',
        'alto',
        'factor_desperdicio',
        'categoria_id',
        'id_lugar',
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

    public function lugar()
    {
        return $this->belongsTo(Lugar::class, 'id_lugar');
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
        return $this->imagen ? asset('storage/materias_primas_contorno/' . basename($this->imagen)) : null;
    }

    // Accessor para calcular precio por m²
    protected function precioM2(): Attribute
    {
        return Attribute::get(function () {
            if ($this->alto > 0 && $this->largo > 0) {
                $area_mm2 = $this->alto * $this->largo;
                $area_m2 = $area_mm2 / 1_000_000;

                return round($this->precioVenta / $area_m2);
            }
            return null;
        });
    }
}
