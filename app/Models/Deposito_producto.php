<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposito_producto extends Model
{
    use HasFactory;
   
    protected $table = 'deposito_productos'; 

    protected $fillable = [
        'deposito_id',
        'producto_id',
        'cantidad',
        'fecha_movimiento',
        'motivo',
    ];

    public $incrementing = false;
    public $timestamps = false;
    protected $primaryKey = null;

}
