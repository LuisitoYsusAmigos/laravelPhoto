<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class MateriaPrimaVarilla extends Model
{
    protected $table = 'materia_prima_varillas';

    protected $fillable = [
        'descripcion',
        'precioCompra',
        'precioVenta',
        'largo',
        'grosor',
        'ancho',
        'factor_desperdicio',
        'categoria_id',
        'sub_categoria_id',
        'stock_global_actual',
        'stock_global_minimo',
        'id_sucursal',
        'imagen',
    ];

    protected $appends = ['precio_m_lineal', 'imagen_url'];

    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
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
        return $this->imagen ? asset('storage/materias_primas/' . basename($this->imagen)) : null;
    }

    // Accessor para precio por metro (en centavos)
    protected function precioMLineal(): Attribute
    {
        return Attribute::get(function () {
            if ($this->largo > 0) {
                $largo_m = $this->largo / 1000; // convertir mm a metros
                return round($this->precioVenta / $largo_m);
            }
            return null;
        });
    }
}
