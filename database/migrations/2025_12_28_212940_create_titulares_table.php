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
        Schema::create('titulares', function (Blueprint $table) {
            $table->id();
            $table->string('tit_nombre', 100);
            $table->string('tit_apellido', 100)->nullable();
            $table->string('tit_ci', 50)->nullable();
            $table->string('tit_direc', 150)->nullable();
            $table->string('tit_telef', 50)->nullable();
            $table->string('tit_email', 150)->nullable();
            $table->string('tit_estado', 20)->default('ACTIVO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('titulares');
    }
};
