<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockVidrio extends Model
{
    use HasFactory;

    protected $table = 'stock_vidrios';

    protected $fillable = [
        'largo',
        'alto',
        'stock',
        'precio',
        'contable',
        'id_materia_prima_vidrio',
    ];

    /**
     * Relación con la materia prima de vidrio.
     */
    public function materiaPrimaVidrio()
    {
        return $this->belongsTo(MateriaPrimaVidrio::class, 'id_materia_prima_vidrio');
    }
}
