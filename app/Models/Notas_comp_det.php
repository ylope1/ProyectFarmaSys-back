<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notas_comp_det extends Model
{
    use HasFactory;
    protected $table = 'notas_comp_det';
    public $incrementing = false;
    protected $primaryKey = ['nota_comp_id', 'producto_id'];
   
    protected $fillable = [
        'nota_comp_id',
        'producto_id',
        'compra_cant',
        'compra_costo',
        'nota_comp_motivo'
    ];
}
