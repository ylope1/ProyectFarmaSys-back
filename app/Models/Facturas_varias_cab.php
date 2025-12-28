<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facturas_varias_cab extends Model
{
    use HasFactory;
    protected $table = 'facturas_varias_cab';

    protected $fillable = [
        'proveedor_id',
        'user_id',
        'sucursal_id',
        'empresa_id',
        'tipo_fact_id',
        'fact_var_fact',
        'fact_var_timbrado',
        'fact_var_fec',
        'fact_var_cant_cta',
        'fact_var_ift',
        'monto_exentas',
        'monto_grav_5',
        'monto_grav_10',
        'monto_iva_5',
        'monto_iva_10',
        'monto_general',
        'fact_var_estado'
    ];
}
