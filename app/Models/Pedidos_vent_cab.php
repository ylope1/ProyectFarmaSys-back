<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedidos_vent_cab extends Model
{
    use HasFactory;
    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'user_id',
        'pedido_vent_fec',
        'pedido_vent_fec_conf',
        'pedido_vent_fec_env',
        'cliente_id',
        'pedido_vent_estado'
    ];
    protected $table = 'pedidos_vent_cab';
}
