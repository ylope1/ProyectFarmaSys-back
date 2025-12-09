<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notas_venta_det extends Model
{
    use HasFactory;
    protected $table = 'notas_venta_det';
    public $incrementing = false;
    protected $primaryKey = ['nota_venta_id', 'producto_id'];
   
    protected $fillable = [
        'nota_venta_id',
        'producto_id',
        'nota_venta_cant',
        'nota_venta_precio',
        'nota_venta_motivo'
    ];
}
