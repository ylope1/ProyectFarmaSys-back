<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Entidades_emisoras extends Model
{
    use HasFactory;
    protected $table = 'entidades_emisoras';

    protected $fillable = [
        'ent_emi_desc',
        'ent_emi_direc',
        'ent_emi_telef',
        'ent_emi_email',
        'ent_emi_estado'
    ];
}
