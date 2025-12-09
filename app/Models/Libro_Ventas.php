<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Libro_Ventas extends Model
{
    use HasFactory;
    protected $table = 'libro_ventas'; // nombre de la tabla

    protected $primaryKey = 'id'; // clave primaria

    protected $fillable = [
        'venta_id',
        'lib_vent_fecha',
        'cli_ruc',
        'lib_vent_tipo_doc',
        'lib_vent_nro_doc',
        'lib_vent_monto',
        'lib_vent_grav_10',
        'lib_vent_iva_10',
        'lib_vent_grav_5',
        'lib_vent_iva_5',
        'lib_vent_exentas',
        'cliente_id',
        'cliente_nombre',
        'impuesto_id',
        'impuesto_desc',
    ];
}
