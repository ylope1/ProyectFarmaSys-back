<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ctas_pagar_fondo_fijo extends Model
{
    use HasFactory;
    protected $table = 'ctas_pagar_fondo_fijo';
    protected $primaryKey = ['id', 'asignacion_ff_id'];
    public $incrementing = false;
    protected $fillable = [
        'id',
        'asignacion_ff_id',
        'rendicion_ff_id',
        'ctas_pagar_ff_monto',
        'ctas_pagar_ff_saldo',
        'ctas_pagar_ff_fec_vto',
        'ctas_pagar_ff_nro_cuota',
        'ctas_pagar_ff_estado',
        'ctas_pagar_ff_tipo'
    ];
}
