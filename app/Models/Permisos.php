<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permisos extends Model
{
    use HasFactory;
    protected $table = 'permisos';

    protected $primaryKey = ['rol_id', 'acceso_id'];

    public $incrementing = false;

    protected $fillable = [
        'rol_id',
        'acceso_id',
        'ver',
        'crear',
        'editar',
        'anular',
        'confirmar',
        'aprobar',
        'rechazar',
        'imprimir'
    ];
}
