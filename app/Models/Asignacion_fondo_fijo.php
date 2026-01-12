<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asignacion_fondo_fijo extends Model
{
    use HasFactory;
    protected $table = 'asignacion_fondo_fijo';

    protected $primaryKey = 'id';

    public $incrementing = true;

    protected $fillable = [
        'user_id',                 // usuario que asigna el fondo fijo
        'proveedor_id',            // beneficiario / responsable
        'empresa_id',
        'sucursal_id',
        'asignacion_ff_monto',
        'asignacion_ff_fecha',
        'asignacion_ff_estado',
        'asignacion_ff_obs'
    ];
}
