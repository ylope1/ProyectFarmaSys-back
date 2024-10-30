<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presup_comp_cab extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'proveedor_id',
        'sucursal_id',
        'empresa_id',
        'pedido_comp_id',
        'presup_comp_fec',
        'presup_comp_fec_aprob',
        'presup_comp_estado'
    ];
    protected $table = 'presup_comp_cab';
}
