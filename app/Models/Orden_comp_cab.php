<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orden_comp_cab extends Model
{
    use HasFactory;
    protected $fillable = [
        'presup_comp_id',
        'proveedor_id',
        'user_id',
        'sucursal_id',
        'empresa_id',
        'pedido_comp_id',
        'tipo_fact_id',
        'orden_comp_fec',
        'orden_comp_fec_aprob',
        'orden_comp_ifv',
        'orden_comp_estado'
    ];
    protected $table = 'orden_comp_cab';
}
