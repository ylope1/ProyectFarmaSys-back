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
        Schema::create('facturas_varias_cab', function (Blueprint $table) {
            $table->id();
            // Relaciones principales
            $table->unsignedBigInteger('proveedor_id');
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('tipo_fact_id');
            $table->foreign('tipo_fact_id')->references('id')->on('tipo_fact')->onDelete('restrict')->onUpdate('cascade');
            // Datos de la factura
            $table->string('fact_var_fact');
            $table->integer('fact_var_timbrado');
            $table->timestamp('fact_var_fec');
            $table->integer('fact_var_cant_cta')->nullable();
            $table->integer('fact_var_ift')->nullable();
            // Montos
            $table->decimal('monto_exentas', 14, 2)->default(0);
            $table->decimal('monto_grav_5', 14, 2)->default(0);
            $table->decimal('monto_grav_10', 14, 2)->default(0);
            $table->decimal('monto_iva_5', 14, 2)->default(0);
            $table->decimal('monto_iva_10', 14, 2)->default(0);
            $table->decimal('monto_general', 14, 2)->default(0);
            // Estado
            $table->string('fact_var_estado')->default('PENDIENTE'); //ANULADO, CONFIRMADO
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas_varias_cab');
    }
};
