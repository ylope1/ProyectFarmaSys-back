<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ventas_cab', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_vent_id')->nullable();
            $table->foreign('pedido_vent_id')->references('id')->on('pedidos_vent_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('cliente_id');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('deposito_id');
            $table->foreign('deposito_id')->references('id')->on('depositos')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('tipo_fact_id');
            $table->foreign('tipo_fact_id')->references('id')->on('tipo_fact')->onDelete('restrict')->onUpdate('cascade');
            $table->string('venta_fact');
            $table->integer('venta_timbrado');
            $table->date('venta_fec');
            $table->integer('venta_cant_cta')->nullable();
            $table->integer('venta_ifv')->nullable();
            $table->decimal('monto_exentas',14,2)->default(0);
            $table->decimal('monto_grav_5',14,2)->default(0);
            $table->decimal('monto_grav_10',14,2)->default(0);
            $table->decimal('monto_iva_5',14,2)->default(0);
            $table->decimal('monto_iva_10',14,2)->default(0);
            $table->decimal('monto_general',14,2)->default(0);
            $table->string('venta_estado')->default('PENDIENTE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ventas_cab');
    }
};
