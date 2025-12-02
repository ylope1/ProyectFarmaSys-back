<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Ajustes_cab;

class Ajustes_cabController extends Controller
{
    public function read()
    {
        return DB::select("
        SELECT 
            ac.*,
            e.empresa_desc,
            s.suc_desc,
            d.deposito_desc,
            to_char(ac.ajuste_fec, 'dd/mm/yyyy') as ajuste_fec,
            u.name as encargado,
            am.ajus_mot_desc
            FROM ajustes_cab ac
            JOIN empresas e ON e.id = ac.empresa_id
            JOIN sucursales s ON s.id = ac.sucursal_id
            JOIN depositos d on d.id = ac.deposito_id
            JOIN users u ON u.id = ac.user_id
            JOIN ajustes_motivos am ON am.id = ac.ajustes_motivos_id
        ");
    }

    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'empresa_id'            => 'required',
            'sucursal_id'           => 'required',
            'deposito_id'           => 'required',
            'user_id'               => 'required',
            'tipo_ajuste'           => 'required',
            'ajustes_motivos_id'    => 'required',
            'ajuste_fec'            => 'required|integer',
            'ajuste_estado'         => 'required',
        ]);
    
        $ajuste = Ajustes_cab::create($datosValidados);

        return response()->json([
            'mensaje'  => 'Compra registrada con éxito',
            'tipo'     => 'success',
            'registro' => $ajuste
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $ajuste = Ajustes_cab::find($id);
        if (!$ajuste) {
            return response()->json([
                'mensaje' => 'Registro no encontrado',
                'tipo'    => 'error'
            ], 404);
        }
        $datosValidados = $request->validate([
            'empresa_id'            => 'required',
            'sucursal_id'           => 'required',
            'deposito_id'           => 'required',
            'user_id'               => 'required',
            'tipo_ajuste'           => 'required',
            'ajustes_motivos_id'    => 'required',
            'ajuste_fec'            => 'required',
            'ajuste_estado'         => 'required',
        ]);

        $ajuste->update($datosValidados);
        return response()->json([
            'mensaje'  => 'Registro modificado con éxito',
            'tipo'     => 'success',
            'registro' => $ajuste
        ], 200);
    }


}
