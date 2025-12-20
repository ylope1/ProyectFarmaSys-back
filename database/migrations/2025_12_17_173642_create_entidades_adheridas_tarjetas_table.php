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
        Schema::create('entidades_adheridas_tarjetas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entidad_adherida_id')->constrained('entidades_adheridas')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('entidad_emisora_id')->constrained('entidades_emisoras')->onUpdate('cascade')->onDelete('restrict');
            $table->foreignId('marca_tarjeta_id')->constrained('marcas_tarjetas')->onUpdate('cascade')->onDelete('restrict');
            $table->string('estado', 20); //activo, inactivo
            $table->timestamps();
            $table->unique(
                ['entidad_adherida_id', 'entidad_emisora_id', 'marca_tarjeta_id'],
                'uniq_ent_adherida_emisora_marca'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entidades_adheridas_tarjetas');
    }
};
