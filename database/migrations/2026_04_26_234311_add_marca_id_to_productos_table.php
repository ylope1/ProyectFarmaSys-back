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
        Schema::table('productos', function (Blueprint $table) {
            // Agregamos la columna marca_id DESPUÉS de impuesto_id (opcional, pero ordenado)
            $table->unsignedBigInteger('marca_id')->nullable()->after('impuesto_id');
            
            // Creamos la foreign key
            $table->foreign('marca_id')->references('id')->on('marcas')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
             // Primero eliminamos la foreign key, luego la columna
            $table->dropForeign(['marca_id']);
            $table->dropColumn('marca_id');
        });
    }
};
