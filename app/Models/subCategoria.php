<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class subCategoria extends Model
{
    protected $table = 'sub_categorias';

    protected $fillable = [
        'nombre'
    ];
}
