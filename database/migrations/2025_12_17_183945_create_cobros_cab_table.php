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
        Schema::create('cobros_cab', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('cliente_id');
            $table->unsignedBigInteger('caja_id');
            $table->unsignedBigInteger('apertura_cierre_id');

            $table->unsignedBigInteger('venta_id')->nullable();

            $table->timestamp('cobro_fecha');
            $table->string('cobro_estado', 20); //pendiente, anulado, cobrado
            $table->decimal('cobro_monto', 15, 2)->default(0);
            $table->string('observacion', 250)->nullable();
            /* Foreign Keys */
            $table->foreign('empresa_id')->references('id')->on('empresas')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('caja_id')->references('id')->on('cajas')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('apertura_cierre_id')->references('id')->on('aperturas_cierres')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('venta_id')->references('id')->on('ventas_cab')->onUpdate('cascade')->onDelete('restrict');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobros_cab');
    }
};
