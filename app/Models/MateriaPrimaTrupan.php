<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MateriaPrimaTrupan extends Model
{
    use HasFactory;
    protected $fillable = [
        'descripcion',
        'grosor',
        'factor_desperdicio',
        'categoria_id',
        'sub_categoria_id',
        'stock_global_actual',
        'stock_global_minimo',
        'id_sucursal',
        'imagen'
    ];
    
    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }
    
    public function subCategoria()
    {
        return $this->belongsTo(SubCategoria::class);
    }
    
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'id_sucursal');
    }
    
    public function getImagenUrlAttribute()
    {
        return $this->imagen ? asset('storage/materias_primas_trupan/' . basename($this->imagen)) : null;
    }
}
