<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orden_pago_det_fact_var extends Model
{
    use HasFactory;
    protected $table = 'orden_pago_det_fact_var';    
    public $incrementing = false;
    protected $primaryKey = null;
    protected $fillable = [
        'orden_pago_id',
        'ctas_pagar_fact_varias_id',
        'op_cuota_nro',
        'op_monto_pagar',
        'op_saldo',
        'op_fecha_vto'
    ];
}
