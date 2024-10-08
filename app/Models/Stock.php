<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;
        protected $fillable = [
        'stock_cant_exist',
        'stock_cant_min',
        'stock_cant_max',
        'deposito_id',
        'producto_id'
    ];
    protected $table = 'stock';
}
