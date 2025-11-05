<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Libro_Compras extends Model
{
    use HasFactory;
    protected $table = 'libro_compras'; // nombre de la tabla

    protected $primaryKey = 'id'; // clave primaria

    protected $fillable = [
        'compra_id',
        'lib_comp_fecha',
        'proveedor_ruc',
        'lib_comp_tipo_doc',
        'lib_comp_nro_doc',
        'lib_comp_monto',
        'lib_comp_grav_10',
        'lib_comp_iva_10',
        'lib_comp_grav_5',
        'lib_comp_iva_5',
        'lib_comp_exentas',
        'proveedor_id',
        'proveedor_desc',
        'impuesto_id',
        'impuesto_desc',
    ];

}
