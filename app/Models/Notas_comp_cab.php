<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notas_comp_cab extends Model
{
    use HasFactory;
    protected $table = 'notas_comp_cab';

    protected $fillable = [
        'compra_id',
        'proveedor_id',
        'user_id',
        'deposito_id',
        'sucursal_id',
        'empresa_id',
        'tipo_fact_id',
        'nota_comp_tipo',
        'nota_comp_fact',
        'nota_comp_timbrado',
        'nota_comp_fec',
        'monto_exentas',
        'monto_grav_5',
        'monto_grav_10',
        'monto_iva_5',
        'monto_iva_10',
        'monto_general',
        'nota_comp_estado',
    ];

}
