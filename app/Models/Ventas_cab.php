<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ventas_cab extends Model
{
    use HasFactory;
    protected $fillable = [
        'pedido_vent_id',
        'cliente_id',
        'user_id',
        'deposito_id',
        'sucursal_id',
        'empresa_id',
        'tipo_fact_id',
        'venta_fact',
        'venta_timbrado',
        'venta_fec',
        'venta_cant_cta',
        'venta_ifv',
        'monto_exentas',
        'monto_grav_5',
        'monto_grav_10',
        'monto_iva_5',
        'monto_iva_10',
        'monto_general',
        'venta_estado'
    ];
    protected $table = 'ventas_cab';
}
