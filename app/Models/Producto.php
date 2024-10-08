<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;
    protected $fillable = [
        'prod_desc',
        'prod_precio_comp',
        'prod_precio_vent',
        'proveedor_id',
        'item_id',
        'impuesto_id'
    ];
}
