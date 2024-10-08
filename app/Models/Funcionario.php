<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;
    protected $fillable = [
        'func_nombre',
        'func_apellido',
        'func_ci',
        'func_direc',
        'func_telef',
        'func_fec_nac',
        'func_fec_baja',
        'func_fec_ing',
        'func_estado',
        'ciudad_id',
        'cargo_id',
        'user_id'
    ];
    /**
     * Define la relacion con el modelo Users.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
