<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remision_comp_det extends Model
{
    use HasFactory;
    protected $fillable = [
        'remision_comp_id',
        'producto_id',
        'rem_comp_cant',
        'rem_comp_costo',
        'rem_comp_obs'
    ];
    protected $primaryKey = ['remision_comp_id','producto_id'];
    public $incrementing = false;
    protected $table = 'remision_comp_det';
}
