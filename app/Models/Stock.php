<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

        protected $fillable = [
        'deposito_id',
        'sucursal_id',
        'producto_id',
        'stock_cant_exist',
        'stock_cant_min',
        'stock_cant_max',
        'cantidad_exceso',
        'fecha_movimiento',
        'motivo'        
    ];
    protected $table = 'stock';
    public $incrementing = false; 
    protected $primaryKey = null; 
}
