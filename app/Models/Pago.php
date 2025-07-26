<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\FormaDePago;
use App\Models\Venta;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'idVenta',
        'idFormaPago',
        'monto',
        'fecha'
    ];

    // Relaciones
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'idVenta');
    }

    public function formaPago()
    {
        return $this->belongsTo(FormaDePago::class, 'idFormaPago');
    }
}
