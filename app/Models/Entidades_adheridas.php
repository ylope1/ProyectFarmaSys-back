<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entidades_adheridas extends Model
{
    use HasFactory;
    protected $table = 'entidades_adheridas';

    protected $fillable = [
        'ent_adhe_desc',
        'ent_adhe_direc',
        'ent_adhe_telef',
        'ent_adhe_email',
        'ent_adhe_estado'
    ];

}
