<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Remision_vent_det extends Model
{
    use HasFactory;
    protected $fillable = [
        'remision_vent_id',
        'producto_id',
        'remision_vent_cant',
        'remision_vent_precio',
        'remision_vent_obs'
    ];
    protected $primaryKey = ['remision_vent_id','producto_id'];
    public $incrementing = false;
    protected $table = 'remision_vent_det';

}
