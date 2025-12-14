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
        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('apertura_cierre_id');
            $table->foreign('apertura_cierre_id')->references('id')->on('aperturas_cierres')->onUpdate('cascade')->onDelete('restrict');
            $table->string('mov_tipo', 10); // INGRESO / EGRESO
            $table->string('mov_concepto', 255);
            $table->decimal('mov_monto', 15, 2);
            $table->string('mov_origen_tipo', 30)->nullable(); // COBRO / VENTA / NOTA
            $table->unsignedBigInteger('origen_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
    }
};
