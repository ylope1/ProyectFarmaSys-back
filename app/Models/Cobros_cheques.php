<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobros_cheques extends Model
{
    use HasFactory;
    protected $table = 'cobros_cheques';

    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'cobro_id',
        'cta_cobrar_id',
        'cta_cobrar_venta_id',
        'entidad_emisora_id',
        'nro_cheque',
        'fecha_vto',
        'estado_cheque'
    ];
}
