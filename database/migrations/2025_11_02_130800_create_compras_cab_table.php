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
        Schema::create('compras_cab', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('orden_comp_id')->nullable();
            $table->foreign('orden_comp_id')->references('id')->on('orden_comp_cab')->onDelete('restrict')->onUpdate('cascade');
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
            $table->string('compra_fact');
            $table->integer('compra_timbrado');
            $table->date('compra_fec');
            $table->date('compra_fec_recep');
            $table->integer('compra_cant_cta')->nullable();
            $table->integer('compra_ifv')->nullable();
            $table->decimal('total_exentas',14,2)->default(0);
            $table->decimal('total_grav_5',14,2)->default(0);
            $table->decimal('total_grav_10',14,2)->default(0);
            $table->decimal('total_iva_5',14,2)->default(0);
            $table->decimal('total_iva_10',14,2)->default(0);
            $table->decimal('total_general',14,2)->default(0);
            $table->string('compra_estado')->default('PENDIENTE');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('compras_cab');
    }
};
