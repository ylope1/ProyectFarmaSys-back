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
        Schema::create('remision_vent_cab', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('venta_id');
            $table->foreign('venta_id')->references('id')->on('ventas_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('cliente_id');
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('deposito_id');
            $table->foreign('deposito_id')->references('id')->on('depositos')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->string('remision_vent_nro')->unique();
            $table->unsignedBigInteger('remision_motivo_id');
            $table->foreign('remision_motivo_id')->references('id')->on('remision_motivo')->onDelete('restrict')->onUpdate('cascade');
            $table->string('remision_vent_repartidor');
            $table->unsignedBigInteger('vehiculo_id');
            $table->foreign('vehiculo_id')->references('id')->on('vehiculos')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('remision_vent_fec');
            $table->timestamp('remision_vent_fec_env')->nullable();
            $table->timestamp('remision_vent_fec_ent')->nullable();
            $table->decimal('monto_exentas',14,2)->default(0);
            $table->decimal('monto_grav_5',14,2)->default(0);
            $table->decimal('monto_grav_10',14,2)->default(0);
            $table->decimal('monto_iva_5',14,2)->default(0);
            $table->decimal('monto_iva_10',14,2)->default(0);
            $table->decimal('monto_general',14,2)->default(0);
            $table->string('remision_vent_estado')->default('PENDIENTE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remision_vent_cab');
    }
};
