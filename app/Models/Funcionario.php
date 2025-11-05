<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Funcionario extends Model
{
    use HasFactory;
    protected $fillable = [
        'persona_id',
        'func_fec_nac',
        'func_fec_baja',
        'func_fec_ing',
        'func_estado',
        'cargo_id',
        'user_id'
    ];
    /**
     * Define la relacion con el modelo Personas.
     */
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }
    /**
     * Define la relacion con el modelo Users.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function cargo()
    {
        return $this->belongsTo(Cargo::class);
    }
}
