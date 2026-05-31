<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Modulos extends Model
{
    use HasFactory;
    protected $table = 'modulos';

    protected $fillable = [
        'mod_desc',
        'mod_orden',
        'mod_estado'
    ];
}
