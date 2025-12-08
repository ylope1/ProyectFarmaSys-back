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
        Schema::create('ctas_cobrar', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('venta_id'); // FK a ventas_cab
            $table->primary(['id','venta_id']);
            $table->foreign('venta_id')->references('id')->on('ventas_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->decimal('ctas_cob_monto', 15, 2); // Total a cobrar
            $table->decimal('ctas_cob_saldo', 15, 2); // Saldo pendiente
            $table->date('ctas_cob_fec_vto'); // Fecha de vencimiento
            $table->integer('ctas_cob_nro_cuota')->default(1); // Número de cuota
            $table->string('ctas_cob_estado', 20); // Estado de la cuenta (Pendiente, Pagada, etc.)
            $table->unsignedBigInteger('tipo_fact_id');  // CONTADO o CREDITO
            $table->foreign('tipo_fact_id')->references('id')->on('tipo_fact')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ctas_cobrar');
    }
};
