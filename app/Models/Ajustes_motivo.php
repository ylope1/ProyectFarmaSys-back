<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ajustes_motivo extends Model
{
    use HasFactory;
    protected $fillable = [
        'ajus_mot_desc'
    ];
}
