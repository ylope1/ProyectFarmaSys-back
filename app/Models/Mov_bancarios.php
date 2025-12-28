<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mov_bancarios extends Model
{
    use HasFactory;
    protected $table = 'mov_bancarios';

    protected $fillable = [
        'cta_bancaria_id',
        'titular_id',
        'mov_banc_fecha',
        'mov_banc_tipo',
        'mov_banc_nro_ref',
        'mov_banc_fec_emision',
        'mov_banc_fec_valor',
        'mov_banc_monto_debito',
        'mov_banc_monto_credito',
        'mov_banc_estado',
        'user_id',
        'sucursal_id',
        'observacion'
    ];
}
