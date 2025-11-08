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
        Schema::dropIfExists('deposito_productos');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('deposito_productos', function (Blueprint $table) {
            $table->unsignedBigInteger('deposito_id');
            $table->unsignedBigInteger('producto_id');
            $table->primary(['deposito_id', 'producto_id']);
            $table->decimal('cantidad');
            $table->timestamp('fecha_movimiento');
            $table->string('motivo');
        });
    }
};
