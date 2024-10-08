<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sucursale extends Model
{
    use HasFactory;
    protected $fillable = [
        'suc_desc',
        'suc_direc',
        'suc_telef',
        'suc_email',
        'pais_id',
        'ciudad_id',
        'empresa_id'
    ];
}
