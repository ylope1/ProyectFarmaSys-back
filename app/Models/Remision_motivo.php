<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remision_motivo extends Model
{
    use HasFactory;
    protected $fillable = ['remision_motivo_desc'];
    protected $table = 'remision_motivo';
}
