<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVentaPersonalizada extends Model
{
    protected $table = 'detalle_venta_personalizadas';

    protected $fillable = [
        'lado_a',
        'lado_b',
        'id_materia_prima_varillas',
        'id_materia_prima_trupans',
        'id_materia_prima_vidrios',
        'id_materia_prima_contornos',
        'id_venta'
    ];

    // Relación con Venta
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }

    // Relación con MateriaPrimaVarilla
    public function materiaPrimaVarilla()
    {
        return $this->belongsTo(MateriaPrimaVarilla::class, 'id_materia_prima_varillas');
    }

    // Relación con MateriaPrimaTrupan
    public function materiaPrimaTrupan()
    {
        return $this->belongsTo(MateriaPrimaTrupan::class, 'id_materia_prima_trupans');
    }

    // Relación con MateriaPrimaVidrio
    public function materiaPrimaVidrio()
    {
        return $this->belongsTo(MateriaPrimaVidrio::class, 'id_materia_prima_vidrios');
    }

    // Relación con MateriaPrimaContorno
    public function materiaPrimaContorno()
    {
        return $this->belongsTo(MateriaPrimaContorno::class, 'id_materia_prima_contornos');
    }
}