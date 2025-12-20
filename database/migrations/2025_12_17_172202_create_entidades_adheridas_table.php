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
        Schema::create('entidades_adheridas', function (Blueprint $table) {
            $table->id();
            $table->string('ent_adhe_desc', 150);
            $table->string('ent_adhe_direc', 200)->nullable();
            $table->string('ent_adhe_telef', 50)->nullable();
            $table->string('ent_adhe_email', 100)->nullable();
            $table->string('ent_adhe_estado', 20); //activo, inactivo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entidades_adheridas');
    }
};
