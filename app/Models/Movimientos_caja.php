<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimientos_caja extends Model
{
    use HasFactory;
    protected $table = 'movimientos_caja';
    protected $fillable = [
        'apertura_cierre_id',
        'mov_tipo',
        'mov_concepto',
        'mov_monto',
        'mov_origen_tipo',
        'origen_id',
    ];
}
