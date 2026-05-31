<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accesos extends Model
{
    use HasFactory;
    protected $table = 'accesos';

    protected $fillable = [
        'modulo_id',
        'acc_desc',
        'acc_ruta',
        'acc_orden',
        'acc_estado'
    ];
}
