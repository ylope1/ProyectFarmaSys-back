<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobros_cab extends Model
{
    use HasFactory;
    protected $table = 'cobros_cab';

    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'user_id',
        'cliente_id',
        'caja_id',
        'apertura_cierre_id',
        'venta_id',
        'cobro_fecha',
        'cobro_estado',
        'cobro_monto',
        'observacion'
    ];
}
