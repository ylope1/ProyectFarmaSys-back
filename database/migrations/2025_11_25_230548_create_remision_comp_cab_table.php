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
        Schema::create('remision_comp_cab', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pedido_comp_id')->nullable();
            $table->foreign('pedido_comp_id')->references('id')->on('pedidos_comp_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('sucursal_origen_id');
            $table->foreign('sucursal_origen_id')->references('id')->on('sucursales')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('sucursal_destino_id');
            $table->foreign('sucursal_destino_id')->references('id')->on('sucursales')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('deposito_origen_id');
            $table->foreign('deposito_origen_id')->references('id')->on('depositos')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('deposito_destino_id');
            $table->foreign('deposito_destino_id')->references('id')->on('depositos')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('restrict')->onUpdate('cascade');
            $table->string('rem_comp_nro')->unique();
            $table->unsignedBigInteger('remision_motivo_id');
            $table->foreign('remision_motivo_id')->references('id')->on('remision_motivo')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('rem_comp_fec');
            $table->timestamp('rem_comp_fec_sal');
            $table->timestamp('rem_comp_fec_recep')->nullable();
            $table->string('chofer');
            $table->unsignedBigInteger('vehiculo_id');
            $table->foreign('vehiculo_id')->references('id')->on('vehiculos')->onDelete('restrict')->onUpdate('cascade');
            $table->decimal('monto_exentas',14,2)->default(0);
            $table->decimal('monto_grav_5',14,2)->default(0);
            $table->decimal('monto_grav_10',14,2)->default(0);
            $table->decimal('monto_iva_5',14,2)->default(0);
            $table->decimal('monto_iva_10',14,2)->default(0);
            $table->decimal('monto_general',14,2)->default(0);
            $table->string('rem_comp_estado')->default('PENDIENTE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remision_comp_cab');

    }
};
