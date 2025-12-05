<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedidos_vent_det extends Model
{
    use HasFactory;
    protected $fillable = [
        'pedido_vent_id',
        'producto_id',
        'pedido_vent_cant',
        'pedido_vent_precio'
    ];
    protected $primaryKey = ['pedido_vent_id','producto_id'];
    public $incrementing = false;
    protected $table = 'pedidos_vent_det';
}
