<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aperturas_cierres extends Model
{
    use HasFactory;
    protected $table = 'aperturas_cierres';
    protected $fillable = [
        'caja_id',
        'user_id',
        'apertura_fec',
        'apertura_monto',
        'cierre_fec',
        'cierre_monto_sistema',
        'cierre_monto_arqueo',
        'cierre_diferencia',
        'estado',
    ];
}
