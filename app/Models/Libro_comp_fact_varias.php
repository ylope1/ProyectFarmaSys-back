<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Libro_comp_fact_varias extends Model
{
    use HasFactory;
    protected $table = 'libro_comp_fact_varias';

    protected $primaryKey = 'id';

    protected $fillable = [
        'factura_varia_id',
        'lib_comp_fv_fecha',
        'proveedor_ruc',
        'lib_comp_fv_tipo_doc',
        'lib_comp_fv_nro_doc',
        'lib_comp_fv_monto',
        'lib_comp_fv_grav_10',
        'lib_comp_fv_iva_10',
        'lib_comp_fv_grav_5',
        'lib_comp_fv_iva_5',
        'lib_comp_fv_exentas',
        'proveedor_id',
        'proveedor_desc',
        'impuesto_id',
        'impuesto_desc'
    ];
}
