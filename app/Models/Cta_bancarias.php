<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cta_bancarias extends Model
{
    use HasFactory;
    protected $fillable = [
        'cta_banc_nro_cuenta',
        'cta_banc_banco',
        'cta_banc_tipo',
        'cta_banc_moneda',
        'cta_banc_estado'
    ];
}
