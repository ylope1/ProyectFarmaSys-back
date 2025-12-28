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
        Schema::create('libro_comp_fact_varias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('factura_varia_id');
            $table->foreign('factura_varia_id')->references('id')->on('facturas_varias_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('lib_comp_fv_fecha');// Fecha del asiento
            $table->string('proveedor_ruc');
            $table->string('lib_comp_fv_tipo_doc')->nullable(); // FACTURA, NOTA, etc.
            $table->string('lib_comp_fv_nro_doc');
           // Montos
            $table->decimal('lib_comp_fv_monto', 15, 2)->default(0);
            $table->decimal('lib_comp_fv_grav_10', 15, 2)->default(0);
            $table->decimal('lib_comp_fv_iva_10', 15, 2)->default(0);
            $table->decimal('lib_comp_fv_grav_5', 15, 2)->default(0);
            $table->decimal('lib_comp_fv_iva_5', 15, 2)->default(0);
            $table->decimal('lib_comp_fv_exentas', 15, 2)->default(0);
            $table->unsignedBigInteger('proveedor_id');// Proveedor
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->onDelete('restrict')->onUpdate('cascade');
            $table->string('proveedor_desc');
            $table->unsignedBigInteger('impuesto_id');// Impuesto
            $table->foreign('impuesto_id')->references('id')->on('tipo_impuestos')->onDelete('restrict')->onUpdate('cascade');
            $table->string('impuesto_desc');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('libro_comp_fact_varias');
    }
};
