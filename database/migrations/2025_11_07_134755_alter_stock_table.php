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
        Schema::table('stock', function (Blueprint $table) {
            // Eliminar clave primaria actual
            $table->dropPrimary();
            // Eliminar la columna id
            $table->dropColumn('id');

            $table->unsignedBigInteger('sucursal_id');
            $table->foreign('sucursal_id')->references('id')->on('sucursales')->onDelete('restrict')->onUpdate('cascade');

            // Agregar nueva clave primaria compuesta
            $table->primary(['deposito_id', 'sucursal_id', 'producto_id']);
            
            // Agregar nuevos campos
            $table->decimal('cantidad_exceso', 15, 2)->nullable()->default(0);
            $table->timestamp('fecha_movimiento')->nullable();
            $table->string('motivo')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock', function (Blueprint $table) {
            $table->dropPrimary(['deposito_id', 'sucursal_id', 'producto_id']);
            
            $table->dropForeign(['sucursal_id']);
            $table->dropColumn(['sucursal_id', 'cantidad_exceso', 'fecha_movimiento', 'motivo']);

            // Restaurar id y clave primaria
            $table->id();
            $table->primary('id');
        });
    }
};
