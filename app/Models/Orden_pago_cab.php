<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orden_pago_cab extends Model
{
    use HasFactory;
    protected $table = 'orden_pago_cab';

    protected $fillable = [
        'empresa_id',
        'user_id',
        'sucursal_id',
        'proveedor_id',
        'orden_pago_fec',
        'orden_pago_fec_aprob',
        'orden_pago_nro_fact',
        'forma_cobro_id',
        'orden_pago_estado'
    ];
}
