<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cobros_det extends Model
{
    use HasFactory;
    protected $fillable = [
        'cobro_id',
        'cta_cobrar_id',
        'cta_cobrar_venta_id',
        'forma_cobro_id',
        'monto_cobro'
    ];
    protected $table = 'cobros_det'; 
    public $incrementing = false; //['cobro_id','cta_cobrar_id','cta_cobrar_venta_id'];
    protected $primaryKey = null;
}
