<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lugar extends Model
{
    protected $table = 'lugars';

    protected $fillable = [
        'nombre'
    ];
}
