<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTrupan extends Model
{
    use HasFactory;

    protected $table = 'stock_trupans';

    protected $fillable = [
        'largo',
        'precio',
        'stock',
        'contable',
        'id_materia_prima_varilla',
    ];

    /**
     * Relación con materia_prima_varilla.
     */
    public function materiaPrimaVarilla()
    {
        return $this->belongsTo(MateriaPrimaVarilla::class, 'id_materia_prima_varilla');
    }
}
