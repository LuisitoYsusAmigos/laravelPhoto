<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'precioProducto',
        'precioPerzonalizado',
        'precioTotal',
        'saldo',
        'recogido',
        'fecha',
        'idCliente',
        'idSucursal',
        'idUsuario',
    ];

    protected $casts = [
        'recogido' => 'boolean',
        'fecha' => 'datetime',
    ];

    // Relación con Cliente
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'idCliente');
    }

    // Relación con Sucursal
    public function sucursal()
    {
        return $this->belongsTo(Sucursal::class, 'idSucursal');
    }
    
    // Relación con Usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'idUsuario');
    }

    // Relación con DetalleVentaProducto
    public function detalleVentaProductos()
    {
        return $this->hasMany(DetalleVentaProducto::class, 'idVenta');
    }

    // Relación con DetalleVentaPersonalizada (CORREGIDA)
    public function detalleVentaPersonalizadas()
    {
        return $this->hasMany(DetalleVentaPersonalizada::class, 'id_venta');
    }
}