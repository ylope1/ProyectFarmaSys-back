<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rubros extends Model
{
    use HasFactory;
    protected $fillable = ['rubro_desc'];
    protected $table = 'rubros';
}
