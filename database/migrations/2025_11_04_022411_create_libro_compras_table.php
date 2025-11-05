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
        Schema::create('libro_compras', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('compra_id'); // FK a compras_cab
            $table->foreign('compra_id')->references('id')->on('compras_cab')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamp('lib_comp_fecha'); // Fecha del asiento

            $table->string('proveedor_ruc'); // RUC proveedor
            $table->string('lib_comp_tipo_doc')->nullable(); // Tipo de documento (factura, nota, etc.)
            $table->string('lib_comp_nro_doc'); // Nro de documento (nro de factura)

            $table->decimal('lib_comp_monto', 15, 2)->default(0);     // Monto total con impuesto
            $table->decimal('lib_comp_grav_10', 15, 2)->default(0);   // Base 10%
            $table->decimal('lib_comp_iva_10', 15, 2)->default(0);    // IVA 10%
            $table->decimal('lib_comp_grav_5', 15, 2)->default(0);    // Base 5%
            $table->decimal('lib_comp_iva_5', 15, 2)->default(0);     // IVA 5%
            $table->decimal('lib_comp_exentas', 15, 2)->default(0);   // Monto exento

            $table->unsignedBigInteger('proveedor_id'); // FK a proveedores
            $table->foreign('proveedor_id')->references('id')->on('proveedores')->onDelete('restrict')->onUpdate('cascade');

            $table->string('proveedor_desc'); // Razón social del proveedor
            $table->unsignedBigInteger('impuesto_id'); // FK a tipo_impuestos
            $table->foreign('impuesto_id')->references('id')->on('tipo_impuestos')->onDelete('restrict')->onUpdate('cascade');
            $table->string('impuesto_desc'); // Descripción del tipo de impuesto 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('libro_compras');
    }
};
