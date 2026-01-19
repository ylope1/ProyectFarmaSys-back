<?php


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaiseController;
use App\Http\Controllers\CiudadeController;
use App\Http\Controllers\EmpresaController;
use App\Http\Controllers\SucursaleController;
use App\Http\Controllers\Tipo_impuestoController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\ProveedoreController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\DepositoController;
use App\Http\Controllers\Deposito_productoController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\CargoController;
use App\Http\Controllers\FuncionarioController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\Ajustes_motivoController;
use App\Http\Controllers\Tipo_factController;
use App\Http\Controllers\Forma_cobroController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\DocumentoController;
use App\Http\Controllers\RubroController;
use App\Http\Controllers\Remision_MotivoController;
use App\Http\Controllers\VehiculosController;
use App\Http\Controllers\ClientesController;
use App\Http\Controllers\Marcas_tarjetasController;
use App\Http\Controllers\Entidades_emisorasController;
use App\Http\Controllers\Entidades_adheridasController;
use App\Http\Controllers\Entidades_adheridas_tarjetasController;
use App\Http\Controllers\TitularesController;
use App\Http\Controllers\Cta_bancariasController;
use App\Http\Controllers\Cta_titularController;
use App\Http\Controllers\Pedido_comp_cabController;
use App\Http\Controllers\Pedido_comp_detController;
use App\Http\Controllers\Presup_comp_cabController;
use App\Http\Controllers\Presup_comp_detController;
use App\Http\Controllers\Orden_comp_cabController;
use App\Http\Controllers\Orden_comp_detController;
use App\Http\Controllers\Compras_cabController;
use App\Http\Controllers\Compras_detController;
use App\Http\Controllers\Notas_comp_cabController;
use App\Http\Controllers\Notas_comp_detController;
use App\Http\Controllers\Remision_comp_cabController;
use App\Http\Controllers\Remision_comp_detController;
use App\Http\Controllers\Ajustes_cabController;
use App\Http\Controllers\Ajustes_detController;
use App\Http\Controllers\Pedidos_vent_cabController;
use App\Http\Controllers\Pedidos_vent_detController;
use App\Http\Controllers\Ventas_cabController;
use App\Http\Controllers\Ventas_detController;
use App\Http\Controllers\Notas_venta_cabController;
use App\Http\Controllers\Notas_venta_detController;
use App\Http\Controllers\Remision_vent_cabController;
use App\Http\Controllers\Remision_vent_detController;
use App\Http\Controllers\Aperturas_cierresController;
use App\Http\Controllers\Arqueo_cajaController;
use App\Http\Controllers\Cobros_cabController;
use App\Http\Controllers\Cobros_detController;
use App\Http\Controllers\Cobros_tarjetasController;
use App\Http\Controllers\Cobros_chequesController;
use App\Http\Controllers\Facturas_varias_cabController;
use App\Http\Controllers\Facturas_varias_detController;
use App\Http\Controllers\Orden_pago_cabController;
use App\Http\Controllers\Orden_pago_detController;
use App\Http\Controllers\Orden_pago_det_fact_varController;
use App\Http\Controllers\Mov_bancariosController;
use App\Http\Controllers\Pago_chequesController;
use App\Http\Controllers\Asignacion_fondo_fijoController;
use App\Http\Controllers\Rendicion_ff_cabController;
use App\Http\Controllers\Rendicion_ff_detController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
Route::get("paises/read",[PaiseController::class,"read"]);
Route::post("paises/create",[PaiseController::class,"store"]);
Route::put("paises/update/{id}",[PaiseController::class,"update"]);
Route::delete("paises/delete/{id}",[PaiseController::class,"destroy"]);
Route::post("paises/buscar", [PaiseController::class, 'buscar']);

Route::get("ciudade/read",[CiudadeController::class,"read"]);
Route::post("ciudade/create",[CiudadeController::class,"store"]);
Route::put("ciudade/update/{id}",[CiudadeController::class,"update"]);
Route::delete("ciudade/delete/{id}",[CiudadeController::class,"destroy"]);
Route::post("ciudade/search", [CiudadeController::class, 'buscar']);

Route::get("empresa/read",[EmpresaController::class,"read"]);
Route::post("empresa/create",[EmpresaController::class,"store"]);
Route::put("empresa/update/{id}",[EmpresaController::class,"update"]);
Route::delete("empresa/delete/{id}",[EmpresaController::class,"destroy"]);
Route::post("empresa/buscar", [EmpresaController::class, 'buscar']);

Route::get("sucursale/read",[SucursaleController::class,"read"]);
Route::post("sucursale/create",[SucursaleController::class,"store"]);
Route::put("sucursale/update/{id}",[SucursaleController::class,"update"]);
Route::delete("sucursale/delete/{id}",[SucursaleController::class,"destroy"]);
Route::post("sucursale/buscar", [SucursaleController::class, 'buscar']);

Route::get("tipo_imp/read",[Tipo_impuestoController::class,"read"]);
Route::post("tipo_imp/create",[Tipo_impuestoController::class,"store"]);
Route::put("tipo_imp/update/{id}",[Tipo_impuestoController::class,"update"]);
Route::delete("tipo_imp/delete/{id}",[Tipo_impuestoController::class,"destroy"]);
Route::post("tipo_imp/search", [Tipo_impuestoController::class, 'buscar']);

Route::get("item/read",[ItemController::class,"read"]);
Route::post("item/create",[ItemController::class,"store"]);
Route::put("item/update/{id}",[ItemController::class,"update"]);
Route::delete("item/delete/{id}",[ItemController::class,"destroy"]);
Route::post("item/search", [ItemController::class, 'buscar']);

Route::get("proveedore/read",[ProveedoreController::class,"read"]);
Route::post("proveedore/create",[ProveedoreController::class,"store"]);
Route::put("proveedore/update/{id}",[ProveedoreController::class,"update"]);
Route::delete("proveedore/delete/{id}",[ProveedoreController::class,"destroy"]);
Route::post("proveedore/buscar", [ProveedoreController::class, 'buscar']);

Route::get("producto/read",[ProductoController::class,"read"]);
Route::post("producto/create",[ProductoController::class,"store"]);
Route::put("producto/update/{id}",[ProductoController::class,"update"]);
Route::delete("producto/delete/{id}",[ProductoController::class,"destroy"]);
Route::post("producto/search", [ProductoController::class, 'buscar']);

Route::get("deposito/read",[DepositoController::class,"read"]);
Route::post("deposito/create",[DepositoController::class,"store"]);
Route::put("deposito/update/{id}",[DepositoController::class,"update"]);
Route::delete("deposito/delete/{id}",[DepositoController::class,"destroy"]);
Route::post("deposito/buscar", [DepositoController::class, 'buscar']);

Route::get("stock/read",[StockController::class,"read"]);
Route::post("stock/create",[StockController::class,"store"]);
Route::put("stock/update/{deposito_id}/{sucursal_id}/{producto_id}",[StockController::class,"update"]);
Route::delete("stock/delete/{deposito_id}/{sucursal_id}/{producto_id}",[StockController::class,"destroy"]);

Route::get("cargo/read",[CargoController::class,"read"]);
Route::post("cargo/create",[CargoController::class,"store"]);
Route::put("cargo/update/{id}",[CargoController::class,"update"]);
Route::delete("cargo/delete/{id}",[CargoController::class,"destroy"]);
Route::post("cargo/search", [CargoController::class, 'buscar']);

Route::get("funcionario/read",[FuncionarioController::class,"read"]);
Route::post("funcionario/create",[FuncionarioController::class,"store"]);
Route::put("funcionario/update/{id}",[FuncionarioController::class,"update"]);
Route::delete("funcionario/delete/{id}",[FuncionarioController::class,"destroy"]);
Route::post("funcionario/search", [FuncionarioController::class,'buscar']);
Route::post("funcionario/buscarChofer", [FuncionarioController::class,'buscarChofer']);
Route::post("funcionario/buscarRepartidor", [FuncionarioController::class,'buscarRepartidor']);

Route::get("marca/read",[MarcaController::class,"read"]);
Route::post("marca/create",[MarcaController::class,"store"]);
Route::put("marca/update/{id}",[MarcaController::class,"update"]);
Route::delete("marca/delete/{id}",[MarcaController::class,"destroy"]);
Route::post("marca/search", [MarcaController::class, 'buscar']);

Route::get("ajuste_motivo/read",[Ajustes_motivoController::class,"read"]);
Route::post("ajuste_motivo/create",[Ajustes_motivoController::class,"store"]);
Route::put("ajuste_motivo/update/{id}",[Ajustes_motivoController::class,"update"]);
Route::delete("ajuste_motivo/delete/{id}",[Ajustes_motivoController::class,"destroy"]);
Route::post("ajuste_motivo/search", [Ajustes_motivoController::class, 'buscar']);

Route::get("tipo_fact/read",[Tipo_factController::class,"read"]);
Route::post("tipo_fact/create",[Tipo_factController::class,"store"]);
Route::put("tipo_fact/update/{id}",[Tipo_factController::class,"update"]);
Route::delete("tipo_fact/delete/{id}",[Tipo_factController::class,"destroy"]);
Route::post("tipo_fact/search",[Tipo_factController::class, 'buscar']);

Route::get("forma_cobro/read",[Forma_cobroController::class,"read"]);
Route::post("forma_cobro/create",[Forma_cobroController::class,"store"]);
Route::put("forma_cobro/update/{id}",[Forma_cobroController::class,"update"]);
Route::delete("forma_cobro/delete/{id}",[Forma_cobroController::class,"destroy"]);
Route::post("forma_cobro/search",[Forma_cobroController::class, 'buscar']);
Route::get("forma_cobro/listarFormasCobro", [Forma_cobroController::class, "listarFormasCobro"]);

Route::get("caja/read",[CajaController::class,"read"]);
Route::post("caja/create",[CajaController::class,"store"]);
Route::put("caja/update/{id}",[CajaController::class,"update"]);
Route::delete("caja/delete/{id}",[CajaController::class,"destroy"]);
Route::post("caja/search",[CajaController::class, 'buscar']);
Route::post("caja/buscarCajas",[CajaController::class, 'buscarCajas']);

Route::get("marcas_tarjetas/read", [Marcas_tarjetasController::class, "read"]);
Route::post("marcas_tarjetas/create", [Marcas_tarjetasController::class, "store"]);
Route::put("marcas_tarjetas/update/{id}", [Marcas_tarjetasController::class, "update"]);
Route::delete("marcas_tarjetas/delete/{id}", [Marcas_tarjetasController::class, "destroy"]);
Route::post("marcas_tarjetas/buscar", [Marcas_tarjetasController::class, "buscar"]);

Route::get("entidades_emisoras/read", [Entidades_emisorasController::class, "read"]);
Route::post("entidades_emisoras/create", [Entidades_emisorasController::class, "store"]);
Route::put("entidades_emisoras/update/{id}", [Entidades_emisorasController::class, "update"]);
Route::delete("entidades_emisoras/delete/{id}", [Entidades_emisorasController::class, "destroy"]);
Route::post("entidades_emisoras/buscar", [Entidades_emisorasController::class, "buscar"]);

Route::get("entidades_adheridas/read", [Entidades_adheridasController::class, "read"]);
Route::post("entidades_adheridas/create", [Entidades_adheridasController::class, "store"]);
Route::put("entidades_adheridas/update/{id}", [Entidades_adheridasController::class, "update"]);
Route::delete("entidades_adheridas/delete/{id}", [Entidades_adheridasController::class, "destroy"]);
Route::post("entidades_adheridas/buscar", [Entidades_adheridasController::class, "buscar"]);

Route::get("entidades_adheridas_tarjetas/read", [Entidades_adheridas_tarjetasController::class, "read"]);
Route::post("entidades_adheridas_tarjetas/create", [Entidades_adheridas_tarjetasController::class, "store"]);
Route::delete("entidades_adheridas_tarjetas/delete/{id}", [Entidades_adheridas_tarjetasController::class, "destroy"]);
Route::post("entidades_adheridas_tarjetas/buscar", [Entidades_adheridas_tarjetasController::class, "buscar"]);

Route::get("documento/read",[DocumentoController::class,"read"]);
Route::post("documento/create",[DocumentoController::class,"store"]);
Route::put("documento/update/{id}",[DocumentoController::class,"update"]);
Route::delete("documento/delete/{id}",[DocumentoController::class,"destroy"]);
Route::post("documento/search",[DocumentoController::class, 'buscar']);

Route::get("rubro/read",[RubroController::class,"read"]);
Route::post("rubro/create",[RubroController::class,"store"]);
Route::put("rubro/update/{id}",[RubroController::class,"update"]);
Route::delete("rubro/delete/{id}",[RubroController::class,"destroy"]);
Route::post("rubro/search",[RubroController::class, 'buscar']);

Route::get("remision_motivo/read",[Remision_MotivoController::class,"read"]);
Route::post("remision_motivo/create",[Remision_motivoController::class,"store"]);
Route::put("remision_motivo/update/{id}",[Remision_motivoController::class,"update"]);
Route::delete("remision_motivo/delete/{id}",[Remision_motivoController::class,"destroy"]);
Route::post("remision_motivo/buscar",[Remision_motivoController::class, 'buscar']);

Route::get("vehiculos/read",[VehiculosController::class,"read"]);
Route::post("vehiculos/create",[VehiculosController::class,"store"]);
Route::put("vehiculos/update/{id}",[VehiculosController::class,"update"]);
Route::delete("vehiculos/delete/{id}",[VehiculosController::class,"destroy"]);
Route::post("vehiculos/buscar",[VehiculosController::class, 'buscar']);

Route::get("clientes/read",[ClientesController::class,"read"]);
Route::post("clientes/create",[ClientesController::class,"store"]);
Route::put("clientes/update/{id}",[ClientesController::class,"update"]);
Route::delete("clientes/delete/{id}",[ClientesController::class,"destroy"]);
Route::post("clientes/buscar",[ClientesController::class, 'buscar']);
Route::post("clientes/buscarClient",[ClientesController::class, 'buscarClient']);

Route::get("titular/read",[TitularesController::class,"read"]);
Route::post("titular/create",[TitularesController::class,"store"]);
Route::put("titular/update/{id}",[TitularesController::class,"update"]);
Route::delete("titular/delete/{id}",[TitularesController::class,"destroy"]);
Route::post("titular/buscar",[TitularesController::class, 'buscar']);

Route::get("cta_bancaria/read",[Cta_bancariasController::class,"read"]);
Route::post("cta_bancaria/create",[Cta_bancariasController::class,"store"]);
Route::put("cta_bancaria/update/{id}",[Cta_bancariasController::class,"update"]);
Route::delete("cta_bancaria/delete/{id}",[Cta_bancariasController::class,"destroy"]);
Route::post("cta_bancaria/buscar",[Cta_bancariasController::class, 'buscar']);

Route::get("cta_titular/read", [Cta_titularController::class, "read"]);
Route::post("cta_titular/create", [Cta_titularController::class, "store"]);
Route::delete("cta_titular/delete/{id}", [Cta_titularController::class, "destroy"]);
Route::post("cta_titular/buscar", [Cta_titularController::class, "buscar"]);

Route::get("pedido_comp_cab/read",[Pedido_comp_cabController::class,"read"]);
Route::post("pedido_comp_cab/create",[Pedido_comp_cabController::class,"store"]);
Route::put("pedido_comp_cab/update/{id}",[Pedido_comp_cabController::class,"update"]);
Route::put("pedido_comp_cab/anular/{id}",[Pedido_comp_cabController::class,"anular"]);
Route::put("pedido_comp_cab/confirmar/{id}",[Pedido_comp_cabController::class,"confirmar"]);
Route::post("pedido_comp_cab/buscar",[Pedido_comp_cabController::class,"buscar"]);

Route::get("pedido_comp_det/read/{id}",[Pedido_comp_detController::class,"read"]);
Route::post("pedido_comp_det/create",[Pedido_comp_detController::class,"store"]);
Route::put("pedido_comp_det/update/{pedido_comp_id}/{producto_id}",[Pedido_comp_detController::class,"update"]);
Route::delete("pedido_comp_det/delete/{pedido_comp_id}/{producto_id}",[Pedido_comp_detController::class,"destroy"]);

Route::get("presup_comp_cab/read",[Presup_comp_cabController::class,"read"]);
Route::post("presup_comp_cab/create",[Presup_comp_cabController::class,"store"]);
Route::put("presup_comp_cab/update/{id}",[Presup_comp_cabController::class,"update"]);
Route::put("presup_comp_cab/anular/{id}",[Presup_comp_cabController::class,"anular"]);
Route::put("presup_comp_cab/confirmar/{id}",[Presup_comp_cabController::class,"confirmar"]);
Route::put("presup_comp_cab/rechazar/{id}",[Presup_comp_cabController::class,"rechazar"]);
Route::put("presup_comp_cab/aprobar/{id}",[Presup_comp_cabController::class,"aprobar"]);
Route::post("presup_comp_cab/buscar",[Presup_comp_cabController::class,"buscar"]);

Route::get("presup_comp_det/read/{id}",[Presup_comp_detController::class,"read"]);
Route::post("presup_comp_det/create",[Presup_comp_detController::class,"store"]);
Route::put("presup_comp_det/update/{presup_comp_id}/{producto_id}",[Presup_comp_detController::class,"update"]);
Route::delete("presup_comp_det/delete/{presup_comp_id}/{producto_id}",[Presup_comp_detController::class,"destroy"]);

Route::get("orden_comp_cab/read",[Orden_comp_cabController::class,"read"]);
Route::post("orden_comp_cab/create",[Orden_comp_cabController::class,"store"]);
Route::put("orden_comp_cab/update/{id}",[Orden_comp_cabController::class,"update"]);
Route::put("orden_comp_cab/anular/{id}",[Orden_comp_cabController::class,"anular"]);
Route::put("orden_comp_cab/confirmar/{id}",[Orden_comp_cabController::class,"confirmar"]);
Route::put("orden_comp_cab/rechazar/{id}",[Orden_comp_cabController::class,"rechazar"]);
Route::put("orden_comp_cab/aprobar/{id}",[Orden_comp_cabController::class,"aprobar"]);
Route::post("orden_comp_cab/buscar",[Orden_comp_cabController::class,"buscar"]);

Route::get("orden_comp_det/read/{id}",[Orden_comp_detController::class,"read"]);
Route::post("orden_comp_det/create",[Orden_comp_detController::class,"store"]);
Route::put("orden_comp_det/update/{orden_comp_id}/{producto_id}",[Orden_comp_detController::class,"update"]);
Route::delete("orden_comp_det/delete/{orden_comp_id}/{producto_id}",[Orden_comp_detController::class,"destroy"]);

Route::get("compras_cab/read",[Compras_cabController::class,"read"]);
Route::post("compras_cab/create",[Compras_cabController::class,"store"]);
Route::put("compras_cab/update/{id}",[Compras_cabController::class,"update"]);
Route::put("compras_cab/anular/{id}",[Compras_cabController::class,"anular"]);
Route::put("compras_cab/confirmar/{id}",[Compras_cabController::class,"confirmar"]);
Route::post("compras_cab/buscar",[Compras_cabController::class,"buscar"]);

Route::get("compras_det/read/{id}",[Compras_detController::class,"read"]);
Route::post("compras_det/create",[Compras_detController::class,"store"]);
Route::put("compras_det/update/{compra_id}/{producto_id}",[Compras_detController::class,"update"]);
Route::delete("compras_det/delete/{compra_id}/{producto_id}",[Compras_detController::class,"destroy"]);

Route::get("notas_comp_cab/read",[Notas_comp_cabController::class,"read"]);
Route::post("notas_comp_cab/create",[Notas_comp_cabController::class,"store"]);
Route::put("notas_comp_cab/update/{id}",[Notas_comp_cabController::class,"update"]);
Route::put("notas_comp_cab/anular/{id}",[Notas_comp_cabController::class,"anular"]);
Route::put("notas_comp_cab/confirmar/{id}",[Notas_comp_cabController::class,"confirmar"]);

Route::get("notas_comp_det/read/{id}",[Notas_comp_detController::class,"read"]);
Route::post("notas_comp_det/create",[Notas_comp_detController::class,"store"]);
Route::put("notas_comp_det/update/{nota_comp_id}/{producto_id}",[Notas_comp_detController::class,"update"]);
Route::delete("notas_comp_det/delete/{nota_comp_id}/{producto_id}",[Notas_comp_detController::class,"destroy"]);

Route::get("remision_comp_cab/read",[Remision_comp_cabController::class,"read"]);
Route::post("remision_comp_cab/create",[Remision_comp_cabController::class,"store"]);
Route::put("remision_comp_cab/update/{id}",[Remision_comp_cabController::class,"update"]);
Route::put("remision_comp_cab/anular/{id}",[Remision_comp_cabController::class,"anular"]);
Route::put("remision_comp_cab/confirmar/{id}",[Remision_comp_cabController::class,"confirmar"]);

Route::get("remision_comp_det/read/{id}",[Remision_comp_detController::class,"read"]);
Route::post("remision_comp_det/create",[Remision_comp_detController::class,"store"]);
Route::put("remision_comp_det/update/{remision_comp_id}/{producto_id}",[Remision_comp_detController::class,"update"]);
Route::delete("remision_comp_det/delete/{remision_comp_id}/{producto_id}",[Remision_comp_detController::class,"destroy"]);

Route::get("ajustes_cab/read",[Ajustes_cabController::class,"read"]);
Route::post("ajustes_cab/create",[Ajustes_cabController::class,"store"]);
Route::put("ajustes_cab/update/{id}",[Ajustes_cabController::class,"update"]);
Route::put("ajustes_cab/anular/{id}",[Ajustes_cabController::class,"anular"]);
Route::put("ajustes_cab/confirmar/{id}",[Ajustes_cabController::class,"confirmar"]);

Route::get("ajustes_det/read/{id}",[Ajustes_detController::class,"read"]);
Route::post("ajustes_det/create",[Ajustes_detController::class,"store"]);
Route::put("ajustes_det/update/{ajuste_id}/{producto_id}",[Ajustes_detController::class,"update"]);
Route::delete("ajustes_det/delete/{ajuste_id}/{producto_id}",[Ajustes_detController::class,"destroy"]);

Route::get("pedidos_vent_cab/read",[Pedidos_vent_cabController::class,"read"]);
Route::post("pedidos_vent_cab/create",[Pedidos_vent_cabController::class,"store"]);
Route::put("pedidos_vent_cab/update/{id}",[Pedidos_vent_cabController::class,"update"]);
Route::put("pedidos_vent_cab/anular/{id}",[Pedidos_vent_cabController::class,"anular"]);
Route::put("pedidos_vent_cab/confirmar/{id}",[Pedidos_vent_cabController::class,"confirmar"]);
Route::post("pedidos_vent_cab/buscar",[Pedidos_vent_cabController::class,"buscar"]);

Route::get("pedidos_vent_det/read/{id}",[Pedidos_vent_detController::class,"read"]);
Route::post("pedidos_vent_det/create",[Pedidos_vent_detController::class,"store"]);
Route::put("pedidos_vent_det/update/{pedido_comp_id}/{producto_id}",[Pedidos_vent_detController::class,"update"]);
Route::delete("pedidos_vent_det/delete/{pedido_comp_id}/{producto_id}",[Pedidos_vent_detController::class,"destroy"]);

Route::get("ventas_cab/read",[Ventas_cabController::class,"read"]);
Route::post("ventas_cab/create",[Ventas_cabController::class,"store"]);
Route::put("ventas_cab/update/{id}",[Ventas_cabController::class,"update"]);
Route::put("ventas_cab/anular/{id}",[Ventas_cabController::class,"anular"]);
Route::put("ventas_cab/confirmar/{id}",[Ventas_cabController::class,"confirmar"]);
Route::post("ventas_cab/buscar",[Ventas_cabController::class,"buscar"]);
Route::post("ventas_cab/buscarVentFactSuc", [Ventas_cabController::class, "buscarVentFactSuc"]);

Route::get("ventas_det/read/{id}",[Ventas_detController::class,"read"]);
Route::post("ventas_det/create",[Ventas_detController::class,"store"]);
Route::put("ventas_det/update/{venta_id}/{producto_id}",[Ventas_detController::class,"update"]);
Route::delete("ventas_det/delete/{venta_id}/{producto_id}",[Ventas_detController::class,"destroy"]);

Route::get("notas_venta_cab/read",[Notas_venta_cabController::class,"read"]);
Route::post("notas_venta_cab/create",[Notas_venta_cabController::class,"store"]);
Route::put("notas_venta_cab/update/{id}",[Notas_venta_cabController::class,"update"]);
Route::put("notas_venta_cab/anular/{id}",[Notas_venta_cabController::class,"anular"]);
Route::put("notas_venta_cab/confirmar/{id}",[Notas_venta_cabController::class,"confirmar"]);
Route::post("notas_venta_cab/buscar",[Notas_venta_cabController::class,"buscar"]);

Route::get("notas_venta_det/read/{id}",[Notas_venta_detController::class,"read"]);
Route::post("notas_venta_det/create",[Notas_venta_detController::class,"store"]);
Route::put("notas_venta_det/update/{nota_venta_id}/{producto_id}",[Notas_venta_detController::class,"update"]);
Route::delete("notas_venta_det/delete/{nota_venta_id}/{producto_id}",[Notas_venta_detController::class,"destroy"]);

Route::get("remision_vent_cab/read",[Remision_vent_cabController::class,"read"]);
Route::post("remision_vent_cab/create",[Remision_vent_cabController::class,"store"]);
Route::put("remision_vent_cab/update/{id}",[Remision_vent_cabController::class,"update"]);
Route::put("remision_vent_cab/anular/{id}",[Remision_vent_cabController::class,"anular"]);
Route::put("remision_vent_cab/confirmar/{id}",[Remision_vent_cabController::class,"confirmar"]);
route::put("remision_vent_cab/enviar/{id}",[Remision_vent_cabController::class,"enviar"]);

Route::get("remision_vent_det/read/{id}",[Remision_vent_detController::class,"read"]);
Route::post("remision_vent_det/create",[Remision_vent_detController::class,"store"]);
Route::put("remision_vent_det/update/{remision_vent_id}/{producto_id}",[Remision_vent_detController::class,"update"]);
Route::delete("remision_vent_det/delete/{remision_vent_id}/{producto_id}",[Remision_vent_detController::class,"destroy"]);

Route::prefix('aperturas_cierres')->group(function () {

    // Listar aperturas/cierres del usuario logueado
    Route::get('read', [Aperturas_cierresController::class, 'read']);

    // Verificar si el usuario tiene una caja abierta
    Route::get('buscar_caja_abierta', [Aperturas_cierresController::class, 'buscarCajaAbierta']);

    // Apertura de caja
    Route::post('abrir', [Aperturas_cierresController::class, 'storeApertura']);

    // Cierre de caja
    Route::post('cerrar', [Aperturas_cierresController::class, 'cerrarCaja']);
});
Route::post("aperturas_cierres/buscarAperturaCaja", [Aperturas_cierresController::class, "buscarAperturaCaja"]);

Route::get("arqueo_caja/read",[Arqueo_cajaController::class,"read"]);
Route::post("arqueo_caja/create",[Arqueo_cajaController::class,"store"]);
Route::put("arqueo_caja/anular/{id}",[Arqueo_cajaController::class,"anular"]);
Route::put("arqueo_caja/confirmar/{id}",[Arqueo_cajaController::class,"confirmar"]);
Route::post("arqueo_caja/buscarArqueo",[Arqueo_cajaController::class,"buscar"]);

Route::get("cobros_cab/read", [Cobros_cabController::class, "read"]);
Route::post("cobros_cab/create", [Cobros_cabController::class, "store"]);
Route::put("cobros_cab/update/{id}", [Cobros_cabController::class, "update"]);
Route::put("cobros_cab/anular/{id}", [Cobros_cabController::class, "anular"]);
Route::put("cobros_cab/anular_confirmado/{id}", [Cobros_cabController::class, "anularConfirmado"]);
Route::put("cobros_cab/confirmar/{id}", [Cobros_cabController::class, "confirmar"]);
Route::get('cobros_cab/total_cheques_apertura', [Cobros_cabController::class, 'totalChequesApertura']);
Route::get('cobros_cab/total_tarjetas_apertura', [Cobros_cabController::class, 'totalTarjetasApertura']);

Route::get("cobros_det/read/{cobro_id}", [Cobros_detController::class, "read"]);
Route::post("cobros_det/create", [Cobros_detController::class, "store"]);
Route::delete("cobros_det/delete",[Cobros_detController::class, "destroy"]);
Route::post("cobros_det/buscarCtaCobro", [Cobros_detController::class, "buscarCtaCobro"]);

Route::get("cobros_tarjetas/read/{cobro_id}", [Cobros_tarjetasController::class, "read"]);
Route::post("cobros_tarjetas/create", [Cobros_tarjetasController::class, "store"]);
Route::delete("cobros_tarjetas/delete", [Cobros_tarjetasController::class, "destroy"]);

Route::get("cobros_cheques/read/{cobro_id}", [Cobros_chequesController::class, "read"]);
Route::post("cobros_cheques/create", [Cobros_chequesController::class, "store"]);
Route::delete("cobros_cheques/delete", [Cobros_chequesController::class, "destroy"]);

Route::get("facturas_varias_cab/read",[Facturas_varias_cabController::class,"read"]);
Route::post("facturas_varias_cab/create",[Facturas_varias_cabController::class,"store"]);
Route::put("facturas_varias_cab/update/{id}",[Facturas_varias_cabController::class,"update"]);
Route::put("facturas_varias_cab/anular/{id}",[Facturas_varias_cabController::class,"anular"]);
Route::put("facturas_varias_cab/confirmar/{id}",[Facturas_varias_cabController::class,"confirmar"]);
//Route::post("facturas_varias_cab/buscar",[Facturas_varias_cabController::class,"buscar"]);

Route::get("facturas_varias_det/read/{id}",[Facturas_varias_detController::class,"read"]);
Route::post("facturas_varias_det/create",[Facturas_varias_detController::class,"store"]);
Route::put("facturas_varias_det/update/{factura_varia_id}/{rubro_id}",[Facturas_varias_detController::class,"update"]);
Route::delete("facturas_varias_det/delete/{factura_varia_id}/{rubro_id}",[Facturas_varias_detController::class,"destroy"]);

Route::get("orden_pago_cab/read",[Orden_pago_cabController::class,"read"]);
Route::post("orden_pago_cab/create",[Orden_pago_cabController::class,"store"]);
Route::put("orden_pago_cab/update/{id}",[Orden_pago_cabController::class,"update"]);
Route::put("orden_pago_cab/anular/{id}",[Orden_pago_cabController::class,"anular"]);
Route::put("orden_pago_cab/confirmar/{id}",[Orden_pago_cabController::class,"confirmar"]);
Route::put("orden_pago_cab/rechazar/{id}",[Orden_pago_cabController::class,"rechazar"]);
Route::put("orden_pago_cab/aprobar/{id}",[Orden_pago_cabController::class,"aprobar"]);
Route::post("orden_pago_cab/buscar",[Orden_pago_cabController::class,"buscar"]);
Route::post("orden_pago_cab/buscarCuotasPendientesProveedor",[Orden_pago_cabController::class,"buscarCuotasPendientesProveedor"]);

Route::get("orden_pago_det/read/{orden_pago_id}",[Orden_pago_detController::class, "read"]);
Route::post("orden_pago_det/create",[Orden_pago_detController::class, "store"]);
Route::delete("orden_pago_det/delete/{orden_pago_id}/{ctas_pagar_id}/{compra_id}",[Orden_pago_detController::class, "destroy"]);

Route::get("orden_pago_det_fact_var/read/{orden_pago_id}", [Orden_pago_det_fact_varController::class, "read"]);
Route::post("orden_pago_det_fact_var/create", [Orden_pago_det_fact_varController::class, "store"]);
Route::delete("orden_pago_det_fact_var/delete/{orden_pago_id}/{ctas_pagar_fact_varias_id}",[Orden_pago_det_fact_varController::class, "destroy"]);

Route::get('mov_bancarios/read',[Mov_bancariosController::class, 'read']);
Route::post('mov_bancarios/create',[Mov_bancariosController::class, 'store']);
Route::put('mov_bancarios/update/{id}',[Mov_bancariosController::class, 'update']);
Route::put('mov_bancarios/anular/{id}',[Mov_bancariosController::class, 'anular']);
Route::put('mov_bancarios/confirmar/{id}',[Mov_bancariosController::class, 'confirmar']);
Route::post('mov_bancarios/buscar',[Mov_bancariosController::class, 'buscar']);

Route::get('pago_cheques/read',[Pago_chequesController::class, 'read']);
Route::post('pago_cheques/create',[Pago_chequesController::class, 'store']);
Route::put("pago_cheques/confirmar/{orden_pago_id}/{mov_bancario_id}", [Pago_chequesController::class, "confirmar"]);
Route::put('pago_cheques/anular/{orden_pago_id}/{mov_bancario_id}',[Pago_chequesController::class, 'anular']);
Route::post('pago_cheques/buscar',[Pago_chequesController::class, 'buscar']);

Route::get("asignacion_fondo_fijo/read", [Asignacion_fondo_fijoController::class, "read"]);
Route::post("asignacion_fondo_fijo/create", [Asignacion_fondo_fijoController::class, "store"]);
Route::put("asignacion_fondo_fijo/confirmar/{id}", [Asignacion_fondo_fijoController::class, "confirmar"]);
Route::put("asignacion_fondo_fijo/inactivar/{id}", [Asignacion_fondo_fijoController::class, "inactivar"]);
Route::put("asignacion_fondo_fijo/activar/{id}", [Asignacion_fondo_fijoController::class, "activar"]);
Route::put("asignacion_fondo_fijo/cerrar/{id}", [Asignacion_fondo_fijoController::class, "cerrar"]);
Route::post("asignacion_fondo_fijo/buscar", [Asignacion_fondo_fijoController::class, "buscar"]);

Route::get("rendicion_ff_cab/read", [Rendicion_ff_cabController::class,"read"]);
Route::post("rendicion_ff_cab/create", [Rendicion_ff_cabController::class,"store"]);
Route::put("rendicion_ff_cab/update/{id}", [Rendicion_ff_cabController::class,"update"]);
Route::put("rendicion_ff_cab/anular/{id}", [Rendicion_ff_cabController::class,"anular"]);
Route::put("rendicion_ff_cab/confirmar/{id}", [Rendicion_ff_cabController::class,"confirmar"]);

Route::get("rendicion_ff_det/read/{rendicion_ff_id}", [Rendicion_ff_detController::class,"read"]);
Route::post("rendicion_ff_det/create", [Rendicion_ff_detController::class,"store"]);
Route::put("rendicion_ff_det/update/{rendicion_ff_id}/{documento_id}", [Rendicion_ff_detController::class,"update"]);
Route::delete("rendicion_ff_det/delete/{rendicion_ff_id}/{documento_id}", [Rendicion_ff_detController::class,"destroy"]);

Route::get("perfil/read", [PerfilController::class,"read"]);
Route::post("perfil/create", [PerfilController::class,"store"]);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('registrarse',[AuthController::class, 'register']);
Route::post('login',[AuthController::class, 'login']);
Route::post("users/search", [AuthController::class, 'buscar']);
Route::middleware(['auth:sanctum'])->group(function () {
Route::get('logout', [AuthController::class, 'logout']);
});
