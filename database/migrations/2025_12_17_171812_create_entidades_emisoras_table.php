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
        Schema::create('entidades_emisoras', function (Blueprint $table) {
            $table->id();
            $table->string('ent_emi_desc', 150);
            $table->string('ent_emi_direc', 200)->nullable();
            $table->string('ent_emi_telef', 50)->nullable();
            $table->string('ent_emi_email', 100)->nullable();
            $table->string('ent_emi_estado', 20); //activo, inactivo
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entidades_emisoras');
    }
};
