<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MateriaPrimaVarilla extends Model
{
    protected $table = 'materia_prima_varillas';

    protected $fillable = [
        'descripcion',
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
}
