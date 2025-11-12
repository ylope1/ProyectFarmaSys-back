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
        Schema::create('notas_comp_cab', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('compra_id');
            $table->foreign('compra_id')->references('id')->on('compras_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('proveedor_id');
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('deposito_id');
            $table->foreign('deposito_id')->references('id')->on('depositos')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('tipo_fact_id');
            $table->foreign('tipo_fact_id')->references('id')->on('tipo_fact')->onDelete('restrict')->onUpdate('cascade');
            $table->enum('nota_comp_tipo', ['CREDITO', 'DEBITO']);
            $table->string('nota_comp_fact');
            $table->integer('nota_comp_timbrado');
            $table->date('nota_comp_fec');
            $table->decimal('monto_exentas',14,2)->default(0);
            $table->decimal('monto_grav_5',14,2)->default(0);
            $table->decimal('monto_grav_10',14,2)->default(0);
            $table->decimal('monto_iva_5',14,2)->default(0);
            $table->decimal('monto_iva_10',14,2)->default(0);
            $table->decimal('monto_general',14,2)->default(0);
            $table->enum('nota_comp_estado', ['PENDIENTE', 'CONFIRMADO', 'ANULADO']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_comp_cab');
    }
};
