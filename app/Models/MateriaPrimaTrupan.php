<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriaPrimaTrupan extends Model
{
    use HasFactory;

    protected $table = 'materia_prima_trupans'; // Nombre de la tabla en la BD

    protected $fillable = [
        'descripcion',
        'grosor',
        'factor_desperdicio',
        'categoria',
        'sub_categoria',
        'stock_global_actual',
        'stock_global_minimo',
        'id_sucursal',
    ];

    // Relación con la tabla Sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }
}
