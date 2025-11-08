<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compras_cab extends Model
{
    use HasFactory;
    protected $fillable = [
        'orden_comp_id',
        'proveedor_id',
        'user_id',
        'deposito_id',
        'sucursal_id',
        'empresa_id',
        'tipo_fact_id',
        'compra_fact',
        'compra_timbrado',
        'compra_fec',
        'compra_fec_recep',
        'compra_cant_cta',
        'compra_ifv',
        'monto_exentas',
        'monto_grav_5',
        'monto_grav_10',
        'monto_iva_5',
        'monto_iva_10',
        'monto_general',
        'compra_estado'
    ];
    protected $table = 'compras_cab';
}
