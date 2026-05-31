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
        Schema::create('permisos', function (Blueprint $table) {
            $table->unsignedBigInteger('rol_id');
            $table->unsignedBigInteger('acceso_id');

            $table->boolean('ver')->default(false);
            $table->boolean('crear')->default(false);
            $table->boolean('modificar')->default(false);
            $table->boolean('anular')->default(false);
            $table->boolean('confirmar')->default(false);
            $table->boolean('aprobar')->default(false);
            $table->boolean('rechazar')->default(false);
            $table->boolean('imprimir')->default(false);
            $table->timestamps();
            $table->primary(['rol_id', 'acceso_id']);
            $table->foreign('rol_id')->references('id')->on('roles')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('acceso_id')->references('id')->on('accesos')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permisos');
    }
};
