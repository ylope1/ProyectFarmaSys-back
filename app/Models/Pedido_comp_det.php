<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido_comp_det extends Model
{
    use HasFactory;
    protected $fillable = [
        'pedido_comp_id',
        'pedido_comp_cant',
        'pedido_comp_precio',
        'producto_id',
        'stock_id',
        'deposito_id'
    ];
    protected $primaryKey = ['pedido_comp_id','producto_id'];
    public $incrementing = false;
    protected $table = 'pedidos_comp_det';
}
