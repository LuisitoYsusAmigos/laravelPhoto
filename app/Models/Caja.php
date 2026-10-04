<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Caja extends Model
{
    protected $table = 'cajas';

    protected $fillable = [
        'detalle',
        'total',
        'ventas',
        'fecha',
        'id_usuario',
        'id_sucursal',
        'observaciones'
    ];

    // Relación con el usuario
    public function usuario()
    {
        return $this->belongsTo(User::class , 'id_usuario');
    }

    // Relación con la sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }
}
