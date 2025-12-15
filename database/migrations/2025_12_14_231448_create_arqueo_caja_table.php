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
        Schema::create('arqueo_caja', function (Blueprint $table) {
            $table->id();
            // Relación con la apertura de caja
            $table->unsignedBigInteger('apertura_cierre_id');
            $table->foreign('apertura_cierre_id')->references('id')->on('aperturas_cierres')->onUpdate('cascade')->onDelete('restrict');
            // Usuario que realiza el arqueo (cajero o supervisor)
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            $table->timestamp('arqueo_fec');
            // Tipo de arqueo
            $table->string('arqueo_tipo', 20);// VERIFICACION | FINAL
            // Montos
            $table->decimal('arqueo_monto_sistema', 15, 2);
            $table->decimal('arqueo_monto', 15, 2);
            $table->decimal('arqueo_diferencia', 15, 2);
            // Estado del arqueo
            $table->string('arqueo_estado', 20)->default('REGISTRADO'); // REGISTRADO | CONFIRMADO | ANULADO
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arqueo_caja');
    }
};
