<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;
    protected $fillable = [
        'empresa_desc',
        'empresa_ruc',
        'empresa_direc',
        'empresa_telef',
        'empresa_email'
    ];
    
}
