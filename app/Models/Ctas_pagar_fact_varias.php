<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ctas_pagar_fact_varias extends Model
{
    use HasFactory;
    protected $table = 'ctas_pagar_fact_varias';

    protected $fillable = [
        'factura_varia_id',
        'cta_pagar_fv_monto',
        'cta_pagar_fv_saldo',
        'cta_pagar_fv_fec_vto',
        'cta_pagar_fv_nro_cuota',
        'cta_pagar_fv_estado',
        'tipo_fact_id'
    ];
}
