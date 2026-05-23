<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Informes_comprasController extends Controller
{
    public function pedidosCompras(Request $request)
    {
        $fechaDesde = $request->fecha_desde;
        $fechaHasta = $request->fecha_hasta;
        $estado = $request->estado;
        $empresaId = $request->empresa_id;
        $sucursalId = $request->sucursal_id;

        $sql = "
            SELECT 
                pcc.id,
                to_char(pcc.pedido_comp_fec, 'DD/MM/YYYY HH24:MI:SS') AS pedido_comp_fec,
                COALESCE(to_char(pcc.pedido_comp_fec_aprob, 'DD/MM/YYYY HH24:MI:SS'), '') AS pedido_comp_fec_aprob,
                pcc.pedido_comp_estado,
                e.empresa_desc,
                s.suc_desc,
                u.name AS encargado,
                COUNT(pcd.producto_id) AS cantidad_items,
                COALESCE(SUM(pcd.pedido_comp_cant), 0) AS total_cantidad
            FROM pedidos_comp_cab pcc
            JOIN empresas e ON e.id = pcc.empresa_id
            JOIN sucursales s ON s.id = pcc.sucursal_id
            JOIN users u ON u.id = pcc.user_id
            LEFT JOIN pedidos_comp_det pcd ON pcd.pedido_comp_id = pcc.id
            WHERE 1=1
        ";

        $parametros = [];

        if ($fechaDesde && $fechaHasta) {
            $sql .= " AND pcc.pedido_comp_fec::date BETWEEN ? AND ? ";
            $parametros[] = $fechaDesde;
            $parametros[] = $fechaHasta;
        }

        if ($estado) {
            $sql .= " AND pcc.pedido_comp_estado = ? ";
            $parametros[] = $estado;
        } else {
            $sql .= " AND pcc.pedido_comp_estado = 'CONFIRMADO' ";
        }

        if ($empresaId) {
            $sql .= " AND pcc.empresa_id = ? ";
            $parametros[] = $empresaId;
        }

        if ($sucursalId) {
            $sql .= " AND pcc.sucursal_id = ? ";
            $parametros[] = $sucursalId;
        }

        $sql .= "
            GROUP BY 
                pcc.id,
                pcc.pedido_comp_fec,
                pcc.pedido_comp_fec_aprob,
                pcc.pedido_comp_estado,
                e.empresa_desc,
                s.suc_desc,
                u.name
            ORDER BY pcc.id DESC
        ";

        $datos = DB::select($sql, $parametros);

        return response()->json($datos, 200);
    }

    public function hojaPreparacionPedido($id)
    {
        $cabecera = DB::select("
            SELECT 
                pcc.id,
                to_char(pcc.pedido_comp_fec, 'DD/MM/YYYY HH24:MI:SS') AS pedido_comp_fec,
                COALESCE(to_char(pcc.pedido_comp_fec_aprob, 'DD/MM/YYYY HH24:MI:SS'), '') AS pedido_comp_fec_aprob,
                pcc.pedido_comp_estado,
                e.empresa_desc,
                s.suc_desc,
                u.name AS encargado
            FROM pedidos_comp_cab pcc
            JOIN empresas e ON e.id = pcc.empresa_id
            JOIN sucursales s ON s.id = pcc.sucursal_id
            JOIN users u ON u.id = pcc.user_id
            WHERE pcc.id = ?
            AND pcc.pedido_comp_estado = 'CONFIRMADO'
            LIMIT 1
        ", [$id]);

        if (empty($cabecera)) {
            return response()->json([
                'mensaje' => 'No se encontró un pedido confirmado con el número ingresado',
                'tipo' => 'error'
            ], 404);
        }

        $detalles = DB::select("
            SELECT 
                p.id AS producto_id,
                p.prod_desc,
                pcd.pedido_comp_cant
            FROM pedidos_comp_det pcd
            JOIN productos p ON p.id = pcd.producto_id
            WHERE pcd.pedido_comp_id = ?
            ORDER BY p.prod_desc
        ", [$id]);

        if (empty($detalles)) {
            return response()->json([
                'mensaje' => 'El pedido seleccionado no tiene productos cargados',
                'tipo' => 'warning'
            ], 404);
        }

        return response()->json([
            'cabecera' => $cabecera[0],
            'detalles' => $detalles
        ], 200);
    }

}
