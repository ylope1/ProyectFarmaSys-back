<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notas_venta_cab extends Model
{
    use HasFactory;
    protected $table = 'notas_venta_cab';

    protected $fillable = [
        'venta_id',
        'cliente_id',
        'user_id',
        'deposito_id',
        'sucursal_id',
        'empresa_id',
        'tipo_fact_id',
        'nota_vent_tipo',
        'nota_vent_fact',
        'nota_vent_timbrado',
        'nota_vent_fec',
        'monto_exentas',
        'monto_grav_5',
        'monto_grav_10',
        'monto_iva_5',
        'monto_iva_10',
        'monto_general',
        'nota_vent_estado',
    ];
}
