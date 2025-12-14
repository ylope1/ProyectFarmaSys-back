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
        Schema::create('aperturas_cierres', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('caja_id');
            $table->foreign('caja_id')->references('id')->on('cajas')->onUpdate('cascade')->onDelete('restrict');
            $table->unsignedBigInteger('user_id'); // usuario que abre/cierra
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');

            // APERTURA
            $table->timestamp('apertura_fec');
            $table->decimal('apertura_monto', 15, 2);

            // CIERRE
            $table->timestamp('cierre_fec')->nullable();
            $table->decimal('cierre_monto_sistema', 15, 2)->default(0);
            $table->decimal('cierre_monto_arqueo', 15, 2)->nullable();
            $table->decimal('cierre_diferencia', 15, 2)->default(0);
            $table->string('estado', 20)->default('ABIERTA'); // ABIERTA / CERRADA
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aperturas_cierres');
    }
};
