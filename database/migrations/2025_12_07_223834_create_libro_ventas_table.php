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
        Schema::create('libro_ventas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('venta_id'); // FK a compras_cab
            $table->foreign('venta_id')->references('id')->on('ventas_cab')->onDelete('restrict')->onUpdate('cascade');

            $table->timestamp('lib_vent_fecha'); // Fecha del asiento

            $table->string('cli_ruc'); // RUC cliente
            $table->string('lib_vent_tipo_doc')->nullable(); // Tipo de documento (factura, nota, etc.)
            $table->string('lib_vent_nro_doc'); // Nro de documento (nro de factura)

            $table->decimal('lib_vent_monto', 15, 2)->default(0);     // Monto total con impuesto
            $table->decimal('lib_vent_grav_10', 15, 2)->default(0);   // Base 10%
            $table->decimal('lib_vent_iva_10', 15, 2)->default(0);    // IVA 10%
            $table->decimal('lib_vent_grav_5', 15, 2)->default(0);    // Base 5%
            $table->decimal('lib_vent_iva_5', 15, 2)->default(0);     // IVA 5%
            $table->decimal('lib_vent_exentas', 15, 2)->default(0);   // Monto exento

            $table->unsignedBigInteger('cliente_id'); // FK a clientes
            $table->foreign('cliente_id')->references('id')->on('clientes')->onDelete('restrict')->onUpdate('cascade');

            $table->string('cliente_nombre'); // Razón social del cliente
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
        Schema::dropIfExists('libro_ventas');
    }
};
