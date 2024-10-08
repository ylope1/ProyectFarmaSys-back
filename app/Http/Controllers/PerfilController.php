<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perfil;

class PerfilController extends Controller
{
    public function read(){
        return Perfil::all();
    }
    public function store(Request $request){
        $datosValidados = $request->validate([
            'perf_desc'=>'required',
            'perf_abreviatura'=>'required'
        ]);
        $perfil = Perfil::create($datosValidados);
        $perfil->save();
        return response()->json([
            'mensaje'=> 'Registro creado con exito',
            'tipo'=>'success',
            'registro'=> $perfil
        ],200);
    }
}
