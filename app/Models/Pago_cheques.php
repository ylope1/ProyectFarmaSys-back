<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago_cheques extends Model
{
    use HasFactory;
    protected $table = 'pago_cheques';

    protected $primaryKey = ['orden_pago_id', 'mov_bancario_id'];

    public $incrementing = false;

    protected $fillable = [
        'orden_pago_id',
        'mov_bancario_id',
        'retira_nombre',
        'retira_ci',
        'retira_telefono',
        'fecha_entrega',
        'pag_cheq_estado'
    ];
}
