<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compras_det extends Model
{
    use HasFactory;
    protected $fillable = [
        'compra_id',
        'producto_id',
        'compra_cant',
        'compra_costo'
    ];
    protected $primaryKey = ['compra_id','producto_id'];
    public $incrementing = false;
    protected $table = 'compras_det';
}
