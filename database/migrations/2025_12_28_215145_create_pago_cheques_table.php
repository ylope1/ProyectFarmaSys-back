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
        Schema::create('pago_cheques', function (Blueprint $table) {
            $table->unsignedBigInteger('orden_pago_id');
            $table->unsignedBigInteger('mov_bancario_id');
            $table->primary(['orden_pago_id', 'mov_bancario_id']);
            $table->foreign('orden_pago_id')->references('id')->on('orden_pago_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('mov_bancario_id')->references('id')->on('mov_bancarios')->onDelete('restrict')->onUpdate('cascade');
            $table->string('retira_nombre', 150);
            $table->string('retira_ci', 50);
            $table->string('retira_telefono', 50)->nullable();
            $table->timestamp('fecha_entrega');
            $table->string('pag_cheq_estado', 20)->default('ENTREGADO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pago_cheques');
    }
};
