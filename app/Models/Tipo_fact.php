<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tipo_fact extends Model
{
    use HasFactory;
    protected $fillable = ['tipo_fact_desc'];
    protected $table = 'tipo_fact';
}
