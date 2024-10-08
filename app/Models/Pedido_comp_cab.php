<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pedido_comp_cab extends Model
{
    use HasFactory;
    protected $fillable = [
        'pedido_comp_fec',
        'pedido_comp_fec_aprob',
        'pedido_comp_estado',
        'empresa_id',
        'sucursal_id',
        'funcionario_id'
    ];
    protected $table = 'pedidos_comp_cab';
}
