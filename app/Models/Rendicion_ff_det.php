<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rendicion_ff_det extends Model
{
    use HasFactory;
    protected $table = 'rendicion_ff_det';
    protected $primaryKey = ['rendicion_ff_id', 'documento_id'];
    public $incrementing = false;
    protected $fillable = [
        'rendicion_ff_id',
        'documento_id',
        'rendicion_ff_det_monto'
    ];
}
