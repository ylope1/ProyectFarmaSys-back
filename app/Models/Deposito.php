<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Deposito extends Model
{
    use HasFactory;
    protected $fillable = [
        'deposito_desc',
        'deposito_direc',
        'deposito_telef',
        'deposito_email',
        'pais_id',
        'ciudad_id',
        'empresa_id',
        'sucursal_id'
    ];

}
