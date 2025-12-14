<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cajas extends Model
{
    use HasFactory;
    protected $fillable = [
        'caja_desc',
        'sucursal_id',
        'empresa_id',
        'user_id'
    ];
    protected $table = 'cajas';
}
