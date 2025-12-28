<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ctas_cobrar extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'venta_id',
        'ctas_cob_monto',
        'ctas_cob_saldo',
        'ctas_cob_fec_vto',
        'ctas_cob_nro_cuota',
        'ctas_cob_estado',
        'tipo_fact_id'
    ];
    protected $primaryKey = ['id','venta_id'];
    public $incrementing = false;
    protected $table = 'ctas_cobrar';
}
