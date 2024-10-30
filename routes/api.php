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
use App\Http\Controllers\Pedido_comp_cabController;
use App\Http\Controllers\Pedido_comp_detController;
use App\Http\Controllers\Presup_comp_cabController;
use App\Http\Controllers\Presup_comp_detController;
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
Route::post("paises/search", [PaiseController::class, 'buscar']);

Route::get("ciudade/read",[CiudadeController::class,"read"]);
Route::post("ciudade/create",[CiudadeController::class,"store"]);
Route::put("ciudade/update/{id}",[CiudadeController::class,"update"]);
Route::delete("ciudade/delete/{id}",[CiudadeController::class,"destroy"]);
Route::post("ciudade/search", [CiudadeController::class, 'buscar']);

Route::get("empresa/read",[EmpresaController::class,"read"]);
Route::post("empresa/create",[EmpresaController::class,"store"]);
Route::put("empresa/update/{id}",[EmpresaController::class,"update"]);
Route::delete("empresa/delete/{id}",[EmpresaController::class,"destroy"]);
Route::post("empresa/search", [EmpresaController::class, 'buscar']);

Route::get("sucursale/read",[SucursaleController::class,"read"]);
Route::post("sucursale/create",[SucursaleController::class,"store"]);
Route::put("sucursale/update/{id}",[SucursaleController::class,"update"]);
Route::delete("sucursale/delete/{id}",[SucursaleController::class,"destroy"]);
Route::post("sucursale/search", [SucursaleController::class, 'buscar']);

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
Route::post("proveedore/search", [ProveedoreController::class, 'buscar']);

Route::get("producto/read",[ProductoController::class,"read"]);
Route::post("producto/create",[ProductoController::class,"store"]);
Route::put("producto/update/{id}",[ProductoController::class,"update"]);
Route::delete("producto/delete/{id}",[ProductoController::class,"destroy"]);
Route::post("producto/search", [ProductoController::class, 'buscar']);

Route::get("deposito/read",[DepositoController::class,"read"]);
Route::post("deposito/create",[DepositoController::class,"store"]);
Route::put("deposito/update/{id}",[DepositoController::class,"update"]);
Route::delete("deposito/delete/{id}",[DepositoController::class,"destroy"]);
Route::post("deposito/search", [DepositoController::class, 'buscar']);

Route::get("stock/read",[StockController::class,"read"]);
Route::post("stock/create",[StockController::class,"store"]);
Route::put("stock/update/{id}",[StockController::class,"update"]);
Route::delete("stock/delete/{id}",[StockController::class,"destroy"]);

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

Route::get("caja/read",[CajaController::class,"read"]);
Route::post("caja/create",[CajaController::class,"store"]);
Route::put("caja/update/{id}",[CajaController::class,"update"]);
Route::delete("caja/delete/{id}",[CajaController::class,"destroy"]);
Route::post("caja/search",[CajaController::class, 'buscar']);

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

Route::get("pedido_comp_cab/read",[Pedido_comp_cabController::class,"read"]);
Route::post("pedido_comp_cab/create",[Pedido_comp_cabController::class,"store"]);
Route::put("pedido_comp_cab/update/{id}",[Pedido_comp_cabController::class,"update"]);
Route::put("pedido_comp_cab/anular/{id}",[Pedido_comp_cabController::class,"anular"]);
Route::put("pedido_comp_cab/confirmar/{id}",[Pedido_comp_cabController::class,"confirmar"]);

Route::get("pedido_comp_det/read/{id}",[Pedido_comp_detController::class,"read"]);
Route::post("pedido_comp_det/create",[Pedido_comp_detController::class,"store"]);
Route::put("pedido_comp_det/update/{pedido_comp_id}/{producto_id}",[Pedido_comp_detController::class,"update"]);
Route::delete("pedido_comp_det/delete/{pedido_comp_id}/{producto_id}",[Pedido_comp_detController::class,"destroy"]);

Route::get("presup_comp_cab/read",[Presup_comp_cabController::class,"read"]);
Route::post("presup_comp_cab/create",[Presup_comp_cabController::class,"store"]);
Route::put("presup_comp_cab/update/{id}",[Presup_comp_cabController::class,"update"]);
Route::put("presup_comp_cab/anular/{id}",[Presup_comp_cabController::class,"anular"]);
Route::put("presup_comp_cab/confirmar/{id}",[Presup_comp_cabController::class,"confirmar"]);

Route::get("perfiles/read",[PerfilController::class,"read"]);
Route::post("perfiles/create",[PerfilController::class,"store"]);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('registrarse',[AuthController::class, 'register']);
Route::post('login',[AuthController::class, 'login']);
Route::post("users/search", [AuthController::class, 'buscar']);
Route::middleware(['auth:sanctum'])->group(function () {
Route::get('logout', [AuthController::class, 'logout']);
});
