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
        Schema::create('orden_pago_det_fact_var', function (Blueprint $table) {
            $table->unsignedBigInteger('orden_pago_id');
            $table->unsignedBigInteger('ctas_pagar_fact_varias_id');
            // Clave primaria compuesta
            $table->primary(['orden_pago_id','ctas_pagar_fact_varias_id']);
            // Claves foraneas
            $table->foreign('orden_pago_id')->references('id')->on('orden_pago_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('ctas_pagar_fact_varias_id')->references('id')->on('ctas_pagar_fact_varias')->onDelete('restrict')->onUpdate('cascade');
            $table->integer('op_cuota_nro');
            $table->bigInteger('op_monto_pagar');
            $table->bigInteger('op_saldo');
            $table->date('op_fecha_vto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_pago_det_fact_var');
    }
};
