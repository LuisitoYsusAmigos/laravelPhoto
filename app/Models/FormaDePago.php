<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormaDePago extends Model
{
    protected $table = 'forma_de_pagos';

    protected $fillable = [
        'nombre'
    ];
}
