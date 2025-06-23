<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockContorno extends Model
{
    use HasFactory;

    protected $table = 'stock_contornos';

    protected $fillable = [
        'largo',
        'alto',
        'stock',
        'precio',
        'contable',
        'id_materia_prima_contorno',
    ];

    /**
     * Relación con la materia prima de contorno.
     */
    public function materiaPrimaContorno()
    {
        return $this->belongsTo(MateriaPrimaContorno::class, 'id_materia_prima_contorno');
    }
}
