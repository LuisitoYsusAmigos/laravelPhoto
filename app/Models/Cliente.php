<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'ci',
        'nombre',
        'apellido',
        'fechaNacimiento',
        'telefono',
        'direccion',
        'email'
    ];

    protected $casts = [
        'fechaNacimiento' => 'date'
    ];
}
