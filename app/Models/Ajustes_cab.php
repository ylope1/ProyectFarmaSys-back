<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ajustes_cab extends Model
{
    use HasFactory;
    protected $fillable = [
        'empresa_id',
        'sucursal_id',
        'deposito_id',
        'user_id',
        'tipo_ajuste',
        'ajustes_motivos_id',
        'ajuste_fec',
        'ajuste_estado'
    ];
    protected $table = 'ajustes_cab';
}
