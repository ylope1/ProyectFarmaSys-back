<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobros_tarjetas extends Model
{
    use HasFactory;
    protected $table = 'cobros_tarjetas';

    public $incrementing = false;
    protected $primaryKey = null;

    protected $fillable = [
        'cobro_id',
        'cta_cobrar_id',
        'cta_cobrar_venta_id',
        'entidad_adherida_tarjeta_id',
        'nro_tarjeta',
        'fecha_vto',
        'estado_tarjeta'
    ];
}
