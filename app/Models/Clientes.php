<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Clientes extends Model
{
    use HasFactory;
    protected $table = 'clientes';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'persona_id',
        'cli_fec_nac',
        'cli_fec_baja',
        'cli_fec_ing',
        'cli_estado',
        'cli_ruc',
        'cli_linea_credito'
    ];

    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }
}
