<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rendicion_ff_cab extends Model
{
    use HasFactory;
    protected $table = 'rendicion_ff_cab';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $fillable = [
        'asignacion_ff_id',
        'user_id',
        'empresa_id',
        'sucursal_id',
        'rendicion_ff_monto_gral',
        'rendicion_ff_fecha',
        'rendicion_ff_estado'
    ];
}
