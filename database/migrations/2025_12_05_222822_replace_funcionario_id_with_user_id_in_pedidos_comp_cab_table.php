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
        Schema::table('pedidos_comp_cab', function (Blueprint $table) {
            // Eliminar la restricción de clave foránea actual
            $table->dropForeign(['funcionario_id']);
            
            // Eliminar la columna funcionario_id
            $table->dropColumn('funcionario_id');
            
            // Agregar la nueva columna user_id
            $table->unsignedBigInteger('user_id');
            // Agregar la nueva restricción de clave foránea
            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict')->onUpdate('cascade');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos_comp_cab', function (Blueprint $table) {
            // Eliminar la restricción de clave foránea de user_id
            $table->dropForeign(['user_id']);
            
            // Eliminar la columna user_id
            $table->dropColumn('user_id');
            
            // Volver a agregar la columna funcionario_id
            $table->unsignedBigInteger('funcionario_id');
            $table->foreign('funcionario_id')->references('id')->on('funcionarios')->onDelete('restrict')->onUpdate('cascade');
        });
    }
};
