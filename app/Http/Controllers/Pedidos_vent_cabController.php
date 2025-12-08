<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Pedidos_vent_cab;

class Pedidos_vent_cabController extends Controller
{
    public function read() 
    {
        return DB::select("
            SELECT 
                pvc.id,
                to_char(pvc.pedido_vent_fec, 'dd/mm/yyyy HH24:mi:ss') AS pedido_vent_fec,
                to_char(pvc.pedido_vent_fec_conf, 'dd/mm/yyyy HH24:mi:ss') AS pedido_vent_fec_conf,
                to_char(pvc.pedido_vent_fec_env, 'dd/mm/yyyy HH24:mi:ss') AS pedido_vent_fec_env,
                pvc.pedido_vent_estado,
                pvc.empresa_id,
                e.empresa_desc,
                pvc.sucursal_id,
                s.suc_desc,
                pvc.user_id,
                u.name AS vendedor,
                pvc.cliente_id,
                per.pers_nombre||' '||per.pers_apellido as nombre_cliente,
                per.pers_ci as cliente_ci,
                c.cli_ruc
            FROM pedidos_vent_cab pvc
            JOIN empresas e ON pvc.empresa_id = e.id
            JOIN sucursales s ON pvc.sucursal_id = s.id
            JOIN users u ON pvc.user_id = u.id
            JOIN clientes c ON pvc.cliente_id = c.id
            JOIN personas per ON c.persona_id = per.id
        ");
    }
    
    public function store(Request $request)
    {
        $datosValidados = $request->validate([
            'pedido_vent_fec'      => 'required',
            'empresa_id'           => 'required|exists:empresas,id',
            'sucursal_id'          => 'required|exists:sucursales,id',
            'user_id'              => 'required|exists:users,id',
            'cliente_id'           => 'required|exists:clientes,id'
        ]);

        $datosValidados['pedido_vent_estado']   = 'PENDIENTE';
        $datosValidados['pedido_vent_fec_conf'] = null;
        $datosValidados['pedido_vent_fec_env']  = null;

        $pedido = Pedidos_vent_cab::create($datosValidados);

        return response()->json([
            'mensaje'  => 'Pedido registrado con éxito',
            'tipo'     => 'success',
            'registro' => $pedido
        ], 200);
    }
    
    public function update(Request $request, $id)
    {
        $pedido = Pedidos_vent_cab::find($id);

        if (!$pedido) {
            return response()->json([
                'mensaje' => 'Pedido no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $datosValidados = $request->validate([
            'pedido_vent_fec'      => 'required',
            'empresa_id'           => 'required|exists:empresas,id',
            'sucursal_id'          => 'required|exists:sucursales,id',
            'user_id'              => 'required|exists:users,id',
            'cliente_id'           => 'required|exists:clientes,id',
            'pedido_vent_estado'   => 'required|string|in:PENDIENTE,CONFIRMADO CLIENTE,ENTREGADO',
            'pedido_vent_fec_conf' => 'nullable',
            'pedido_vent_fec_env'  => 'nullable',
        ]);

        // Convertir strings vacíos a null (evita error en PostgreSQL)
        if ($request->input('pedido_vent_fec_conf') === '') {
            $datosValidados['pedido_vent_fec_conf'] = null;
        }
        if ($request->input('pedido_vent_fec_env') === '') {
            $datosValidados['pedido_vent_fec_env'] = null;
        }

        $pedido->update($datosValidados);

        return response()->json([
            'mensaje'  => 'Pedido actualizado con éxito',
            'tipo'     => 'success',
            'registro' => $pedido
        ], 200);
    }
    
    public function destroy($id)
    {
        $pedido = Pedidos_vent_cab::find($id);

        if (!$pedido) {
            return response()->json([
                'mensaje' => 'Pedido no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        $pedido->delete();

        return response()->json([
            'mensaje' => 'Pedido eliminado con éxito',
            'tipo'    => 'success'
        ], 200);
    }

    public function anular(Request $request, $id)
    {
        $pedido = Pedidos_vent_cab::find($id);

        if (!$pedido) {
            return response()->json([
                'mensaje' => 'Pedido no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        // Validar solo los campos necesarios
        $datosValidados = $request->validate([
            'pedido_vent_fec'       => 'required',
            'empresa_id'            => 'required',
            'sucursal_id'           => 'required',
            'user_id'               => 'required',
            'cliente_id'            => 'required'
        ]);

        $pedido->update([
            ...$datosValidados,
            'pedido_vent_estado'    => 'ANULADO',
            'pedido_vent_fec_conf'  => null,
            'pedido_vent_fec_env'   => null
        ]);

        return response()->json([
            'mensaje' => 'Pedido anulado con éxito',
            'tipo'    => 'success',
            'registro'=> $pedido
        ], 200);
    }

    public function confirmar(Request $request, $id)
    {
        $pedido = Pedidos_vent_cab::find($id);

        if (!$pedido) {
            return response()->json([
                'mensaje' => 'Pedido no encontrado',
                'tipo'    => 'error'
            ], 404);
        }

        // Validación estricta: la fecha de confirmación debe venir cargada
        $datosValidados = $request->validate([
            'pedido_vent_fec'       => 'required',
            'pedido_vent_fec_conf'  => 'required', // obligatorio solo aquí
            'empresa_id'            => 'required',
            'sucursal_id'           => 'required',
            'user_id'               => 'required',
            'cliente_id'            => 'required',
            'pedido_vent_estado'    => 'required|in:CONFIRMADO' // solo permitimos este estado aquí
        ]);
        // Convertir strings vacíos a null (evita error en PostgreSQL)
        if ($request->input('pedido_vent_fec_env') === '') {
            $datosValidados['pedido_vent_fec_env'] = null;
        }

        $pedido->update($datosValidados);

        return response()->json([
            'mensaje'  => 'Pedido confirmado correctamente',
            'tipo'     => 'success',
            'registro' => $pedido
        ], 200);
    }
    
}
