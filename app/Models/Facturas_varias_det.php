<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facturas_varias_det extends Model
{
    use HasFactory;
    protected $table = 'facturas_varias_det';

    protected $primaryKey = ['factura_varia_id', 'rubro_id'];
    public $incrementing = false;

    protected $fillable = [
        'factura_varia_id',
        'rubro_id',
        'fact_var_cant',
        'fact_var_monto',
        'fact_var_tipo_iva'
    ];
}
