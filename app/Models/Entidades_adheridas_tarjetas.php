<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entidades_adheridas_tarjetas extends Model
{
    use HasFactory;
    protected $table = 'entidades_adheridas_tarjetas';

    protected $fillable = [
        'entidad_adherida_id',
        'entidad_emisora_id',
        'marca_tarjeta_id',
        'estado'
    ];
}
