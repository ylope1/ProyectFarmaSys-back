<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedore extends Model
{
    use HasFactory;
    protected $fillable = [
        'proveedor_desc',
        'proveedor_ruc',
        'proveedor_tipo',
        'proveedor_direc',
        'proveedor_telef',
        'proveedor_email',
        'pais_id',
        'ciudad_id'
    ];
}
