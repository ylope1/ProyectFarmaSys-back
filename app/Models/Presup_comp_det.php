<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Presup_comp_det extends Model
{
    use HasFactory;

    protected $fillable = [
    'presup_comp_id',
    'producto_id',
    'presup_comp_cant',
    'presup_comp_costo'
];
    protected $primaryKey = ['presup_comp_id','producto_id'];
    public $incrementing = false;
    protected $table = 'presup_comp_det';
}
