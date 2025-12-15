<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Arqueo_caja extends Model
{
    use HasFactory;
    protected $table = 'arqueo_caja';
     protected $fillable = [
        'apertura_cierre_id',
        'user_id',
        'arqueo_fec',
        'arqueo_tipo',
        'arqueo_monto_sistema',
        'arqueo_monto',
        'arqueo_diferencia',
        'arqueo_estado'
    ];
    
}
