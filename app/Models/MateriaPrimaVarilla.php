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
        'categoria',
        'sub_categoria',
        'stock_global_actual',
        'stock_global_minimo',
        'id_sucursal'
    ];

    // Relación con la sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }
}
