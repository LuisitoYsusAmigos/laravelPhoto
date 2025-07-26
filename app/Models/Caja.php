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
    ];

    // Relación con el usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }
}
