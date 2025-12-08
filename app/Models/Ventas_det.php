<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ventas_det extends Model
{
    use HasFactory;
    protected $fillable = [
        'venta_id',
        'producto_id',
        'venta_cant',
        'venta_precio'
    ];
    protected $primaryKey = ['venta_id','producto_id'];
    public $incrementing = false;
    protected $table = 'ventas_det';
}
