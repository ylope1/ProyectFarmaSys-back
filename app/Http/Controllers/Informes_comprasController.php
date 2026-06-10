<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\VerificaPermisos;

class Informes_comprasController extends Controller
{
    use VerificaPermisos;
    private $rutaInforme = 'informes/movimientos_compras/compras/';

    public function pedidosCompras(Request $request)
    {
        $permiso = $this->verificarPermiso($this->rutaInforme, 'ver');
        if ($permiso) {
            return $permiso;
        }

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
        $permiso = $this->verificarPermiso($this->rutaInforme, 'ver');
        if ($permiso) {
            return $permiso;
        }

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

    public function presupuestosCompras(Request $request)
    {
        $permiso = $this->verificarPermiso($this->rutaInforme, 'ver');
        if ($permiso) {
            return $permiso;
        }

        $fechaDesde = $request->fecha_desde;
        $fechaHasta = $request->fecha_hasta;
        $estado = $request->estado;
        $empresaId = $request->empresa_id;
        $sucursalId = $request->sucursal_id;
        $proveedorId = $request->proveedor_id;

        $sql = "
            SELECT 
                prc.id,
                to_char(prc.presup_comp_fec, 'DD/MM/YYYY HH24:MI:SS') AS presup_comp_fec,
                COALESCE(to_char(prc.presup_comp_fec_aprob, 'DD/MM/YYYY HH24:MI:SS'), '') AS presup_comp_fec_aprob,
                prc.presup_comp_estado,
                pr.proveedor_desc,
                e.empresa_desc,
                s.suc_desc,
                u.name AS encargado,
                prc.pedido_comp_id,
                COUNT(prd.producto_id) AS cantidad_items,
                COALESCE(SUM(prd.presup_comp_cant), 0) AS total_cantidad,
                COALESCE(SUM(prd.presup_comp_cant * prd.presup_comp_costo), 0) AS total_presupuesto
            FROM presup_comp_cab prc
            JOIN proveedores pr ON pr.id = prc.proveedor_id
            JOIN empresas e ON e.id = prc.empresa_id
            JOIN sucursales s ON s.id = prc.sucursal_id
            JOIN users u ON u.id = prc.user_id
            LEFT JOIN presup_comp_det prd ON prd.presup_comp_id = prc.id
            WHERE 1=1
        ";

        $parametros = [];

        if ($fechaDesde && $fechaHasta) {
            $sql .= " AND prc.presup_comp_fec::date BETWEEN ? AND ? ";
            $parametros[] = $fechaDesde;
            $parametros[] = $fechaHasta;
        }

        if ($estado) {
            $sql .= " AND prc.presup_comp_estado = ? ";
            $parametros[] = $estado;
        }

        if ($empresaId) {
            $sql .= " AND prc.empresa_id = ? ";
            $parametros[] = $empresaId;
        }

        if ($sucursalId) {
            $sql .= " AND prc.sucursal_id = ? ";
            $parametros[] = $sucursalId;
        }

        if ($proveedorId) {
            $sql .= " AND prc.proveedor_id = ? ";
            $parametros[] = $proveedorId;
        }

        $sql .= "
            GROUP BY 
                prc.id,
                prc.presup_comp_fec,
                prc.presup_comp_fec_aprob,
                prc.presup_comp_estado,
                pr.proveedor_desc,
                e.empresa_desc,
                s.suc_desc,
                u.name,
                prc.pedido_comp_id
            ORDER BY prc.id DESC
        ";

        $datos = DB::select($sql, $parametros);

        return response()->json($datos, 200);
    }

    public function hojaPresupuestoCompra($id)
    {
        $permiso = $this->verificarPermiso($this->rutaInforme, 'ver');
        if ($permiso) {
            return $permiso;
        }
        $cabecera = DB::select("
            SELECT 
                prc.id,
                to_char(prc.presup_comp_fec, 'DD/MM/YYYY HH24:MI:SS') AS presup_comp_fec,
                COALESCE(to_char(prc.presup_comp_fec_aprob, 'DD/MM/YYYY HH24:MI:SS'), '') AS presup_comp_fec_aprob,
                prc.presup_comp_estado,
                pr.proveedor_desc,
                e.empresa_desc,
                s.suc_desc,
                u.name AS encargado,
                prc.pedido_comp_id
            FROM presup_comp_cab prc
            JOIN proveedores pr ON pr.id = prc.proveedor_id
            JOIN empresas e ON e.id = prc.empresa_id
            JOIN sucursales s ON s.id = prc.sucursal_id
            JOIN users u ON u.id = prc.user_id
            WHERE prc.id = ?
            LIMIT 1
        ", [$id]);

        if (empty($cabecera)) {
            return response()->json([
                'mensaje' => 'No se encontró el presupuesto con el número ingresado',
                'tipo' => 'error'
            ], 404);
        }

        $detalles = DB::select("
            SELECT 
                p.id AS producto_id,
                p.prod_desc,
                prd.presup_comp_cant,
                prd.presup_comp_costo,
                (prd.presup_comp_cant * prd.presup_comp_costo) AS subtotal
            FROM presup_comp_det prd
            JOIN productos p ON p.id = prd.producto_id
            WHERE prd.presup_comp_id = ?
            ORDER BY p.prod_desc
        ", [$id]);

        if (empty($detalles)) {
            return response()->json([
                'mensaje' => 'El presupuesto seleccionado no tiene productos cargados',
                'tipo' => 'warning'
            ], 404);
        }

        return response()->json([
            'cabecera' => $cabecera[0],
            'detalles' => $detalles
        ], 200);
    }
}
