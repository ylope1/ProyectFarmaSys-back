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
        Schema::create('ctas_pagar', function (Blueprint $table) {
            $table->unsignedBigInteger('id');
            $table->unsignedBigInteger('compra_id'); // FK a compras_cab
            $table->primary(['id','compra_id']);
            $table->foreign('compra_id')->references('id')->on('compras_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->decimal('monto', 15, 2); // Total a pagar
            $table->decimal('saldo', 15, 2); // Saldo pendiente
            $table->date('fecha_vencimiento'); // Fecha de vencimiento
            $table->integer('nro_cuota')->default(1); // Número de cuota
            $table->string('estado', 20); // Estado de la cuenta (Pendiente, Pagada, etc.)
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
        Schema::dropIfExists('ctas_pagar');
    }
};
