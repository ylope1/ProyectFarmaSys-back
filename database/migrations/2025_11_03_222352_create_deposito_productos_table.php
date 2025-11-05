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
        Schema::create('deposito_productos', function (Blueprint $table) {
            
            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('deposito_id');
            $table->integer('cantidad');
            $table->timestamp('fecha_movimiento')->useCurrent(); // para saber cuándo se derivó al depósito
            $table->string('motivo')->nullable(); // opcional, puede registrar motivo del movimiento
            $table->timestamps();

            $table->primary(['deposito_id', 'producto_id']); // PK compuesta
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('deposito_id')->references('id')->on('depositos')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deposito_productos');
    }
};
