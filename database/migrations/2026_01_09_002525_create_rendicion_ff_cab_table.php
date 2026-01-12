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
        Schema::create('rendicion_ff_cab', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asignacion_ff_id');// Relación con la asignación de fondo fijo
            $table->foreign('asignacion_ff_id')->references('id')->on('asignacion_fondo_fijo')->onUpdate('cascade')->onDelete('restrict');
            $table->unsignedBigInteger('user_id');// Usuario que rinde (normalmente el responsable)
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('restrict');
            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onUpdate('cascade')->onDelete('restrict');
            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onUpdate('cascade')->onDelete('restrict');
            $table->decimal('rendicion_ff_monto_gral',14,2); // Monto total rendido
            $table->timestamp('rendicion_ff_fecha');
            $table->string('rendicion_ff_estado', 15)->default('PENDIENTE'); // PENDIENTE | CONFIRMADA | RECHAZADA
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rendicion_ff_cab');
    }
};
