<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remision_comp_cab extends Model
{
    use HasFactory;
    protected $fillable = [
        'pedido_comp_id',
        'user_id',
        'sucursal_origen_id',
        'sucursal_destino_id',
        'deposito_origen_id',
        'deposito_destino_id',
        'empresa_id',
        'rem_comp_nro',
        'remision_motivo_id',
        'rem_comp_fec',
        'rem_comp_fec_sal',
        'rem_comp_fec_recep',        
        'chofer',
        'vehiculo_id',
        'monto_exentas',
        'monto_grav_5',
        'monto_grav_10',
        'monto_iva_5',
        'monto_iva_10',
        'monto_general',
        'rem_comp_estado'
    ];
    protected $table = 'remision_comp_cab';
}
