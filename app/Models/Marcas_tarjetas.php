<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marcas_tarjetas extends Model
{
    use HasFactory;
    protected $table = 'marcas_tarjetas';
    protected $fillable = [
        'marca_desc',
        'marca_estado'
    ];
}
