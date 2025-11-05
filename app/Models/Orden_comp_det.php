<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orden_comp_det extends Model
{
    use HasFactory;
    protected $fillable = [
        'orden_comp_id',
        'producto_id',
        'orden_comp_cant',
        'orden_comp_costo'
    ];
    protected $primaryKey = ['orden_comp_id','producto_id'];
    public $incrementing = false;
    protected $table = 'orden_comp_det';
}
