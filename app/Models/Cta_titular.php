<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cta_titular extends Model
{
    use HasFactory;
    protected $table = 'cta_titular';

    protected $primaryKey = ['cta_bancaria_id', 'titular_id'];

    public $incrementing = false;

    protected $fillable = [
        'cta_bancaria_id',
        'titular_id',
        'rol',
        'firma_habilitada',
        'estado'
    ];
}
