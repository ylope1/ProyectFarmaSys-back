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
        Schema::create('ctas_pagar_fact_varias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('factura_varia_id');
            $table->foreign('factura_varia_id')->references('id')->on('facturas_varias_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->decimal('cta_pagar_fv_monto', 15, 2); // Total a pagar
            $table->decimal('cta_pagar_fv_saldo', 15, 2); // Saldo pendiente
            $table->date('cta_pagar_fv_fec_vto'); // Fecha de vencimiento
            $table->integer('cta_pagar_fv_nro_cuota')->default(1); // Número de cuota
            $table->string('cta_pagar_fv_estado', 20); // PENDIENTE, PAGADA, ANULADA, etc.
            $table->unsignedBigInteger('tipo_fact_id'); // CONTADO / CREDITO
            $table->foreign('tipo_fact_id')->references('id')->on('tipo_fact')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ctas_pagar_fact_varias');
    }
};
