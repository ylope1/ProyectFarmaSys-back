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
        Schema::table('funcionarios', function (Blueprint $table) {
            
            // Eliminamos los campos que ahora estarán en personas
            $table->dropColumn(['func_nombre','func_apellido','func_ci','func_direc','func_telef']);
            // Eliminamos también ciudad_id (porque ya está en personas)
            $table->dropForeign(['ciudad_id']);
            $table->dropColumn('ciudad_id');

            // Agregamos la relación con persona
            $table->unsignedBigInteger('persona_id')->after('id');
            $table->foreign('persona_id')->references('id')->on('personas')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->dropForeign(['persona_id']);
            $table->dropColumn('persona_id');
            // Revertir cambios
            $table->string('func_nombre', 35);
            $table->string('func_apellido', 35);
            $table->integer('func_ci');
            $table->string('func_direc', 35);
            $table->string('func_telef', 15);

            // 3. Restauramos ciudad_id
            $table->unsignedBigInteger('ciudad_id');
            $table->foreign('ciudad_id')
              ->references('id')
              ->on('ciudades')
              ->onDelete('restrict')
              ->onUpdate('cascade');           
        });
    }
};
