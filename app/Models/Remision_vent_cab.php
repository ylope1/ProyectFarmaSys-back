<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remision_vent_cab extends Model
{
    use HasFactory;
    protected $fillable = [
        'venta_id',
        'cliente_id',
        'empresa_id',
        'sucursal_id',
        'deposito_id',
        'user_id',
        'remision_vent_nro',
        'remision_motivo_id',
        'remision_vent_repartidor',
        'vehiculo_id',
        'remision_vent_fec',
        'remision_vent_fec_env',
        'remision_vent_fec_ent',
        'monto_exentas',
        'monto_grav_5',
        'monto_grav_10',
        'monto_iva_5',
        'monto_iva_10',
        'monto_general',
        'remision_vent_estado',
    ];
    protected $table = 'remision_vent_cab';
}
