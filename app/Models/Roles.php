<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roles extends Model
{
    use HasFactory;
    protected $table = 'roles';

    protected $fillable = [
        'rol_desc',
        'rol_abreviatura',
        'rol_estado'
    ];
}
