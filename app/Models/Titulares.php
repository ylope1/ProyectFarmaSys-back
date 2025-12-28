<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Titulares extends Model
{
    use HasFactory;
    protected $table = 'titulares';

    protected $fillable = [
        'tit_nombre',
        'tit_apellido',
        'tit_ci',
        'tit_direc',
        'tit_telef',
        'tit_email',
        'tit_estado'
    ];
}
