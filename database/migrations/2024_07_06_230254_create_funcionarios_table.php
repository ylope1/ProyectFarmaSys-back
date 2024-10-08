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
        Schema::create('funcionarios', function (Blueprint $table) {
            $table->id();
            $table->string('func_nombre',35);
            $table->string('func_apellido',35);
            $table->integer('func_ci');
            $table->string('func_direc',35);
            $table->string('func_telef',15);
            $table->timestamp('func_fec_nac');
            $table->timestamp('func_fec_baja');
            $table->timestamp('func_fec_ing');
            $table->string('func_estado',10);
            $table->unsignedBigInteger('ciudad_id');
            $table->foreign('ciudad_id')->references('id')->on('ciudades')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('cargo_id');
            $table->foreign('cargo_id')->references('id')->on('cargos')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('funcionarios');
    }
};
