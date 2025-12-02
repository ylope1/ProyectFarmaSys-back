<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ajustes_det extends Model
{
    use HasFactory;
    protected $fillable = [
        'ajuste_id',
        'producto_id',
        'ajuste_cant',
        'ajuste_costo',
        'item_id'
    ];
    protected $primaryKey = ['ajuste_cab_id','producto_id'];
    public $incrementing = false;
    protected $table = 'ajustes_det';
}
