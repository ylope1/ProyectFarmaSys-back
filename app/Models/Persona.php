<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Persona extends Model
{
    use HasFactory;
    protected $fillable = [
        'pers_nombre',
        'pers_apellido',
        'pers_ci',
        'pers_direc',
        'pers_telef',
        'pers_email',
        'pais_id',
        'ciudad_id'
    ];
    public function funcionario()
    {
        return $this->hasOne(Funcionario::class, 'persona_id');
    }
}
