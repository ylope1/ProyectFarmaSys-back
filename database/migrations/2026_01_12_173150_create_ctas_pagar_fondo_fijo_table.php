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
        Schema::create('ctas_pagar_fondo_fijo', function (Blueprint $table) {
            $table->unsignedBigInteger('id'); // correlativo por asignación
            $table->unsignedBigInteger('asignacion_ff_id');
            $table->primary(['id', 'asignacion_ff_id']);// PK compuesta
            $table->foreign('asignacion_ff_id')->references('id')->on('asignacion_fondo_fijo')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('rendicion_ff_id')->nullable();
            $table->foreign('rendicion_ff_id')->references('id')->on('rendicion_ff_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->decimal('ctas_pagar_ff_monto', 15, 2);
            $table->decimal('ctas_pagar_ff_saldo', 15, 2);
            $table->date('ctas_pagar_ff_fec_vto');
            $table->integer('ctas_pagar_ff_nro_cuota')->default(1);
            $table->string('ctas_pagar_ff_estado', 20); // PENDIENTE | PAGADA
            $table->string('ctas_pagar_ff_tipo', 15); //TIPO DE MOVIMIENTO: PROVISION | REPOSICION
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ctas_pagar_fondo_fijo');
    }
};
