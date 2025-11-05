<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ctas_pagar extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'compra_id',
        'monto',
        'saldo',
        'fecha_vencimiento',
        'nro_cuota',
        'estado',
        'tipo_fact_id'
    ];
    protected $table = 'ctas_pagar';
}
