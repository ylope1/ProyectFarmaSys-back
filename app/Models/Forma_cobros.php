<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Forma_cobros extends Model
{
    use HasFactory;
    protected $fillable = ['forma_cob_desc'];
    protected $table = 'forma_cobros';
}
