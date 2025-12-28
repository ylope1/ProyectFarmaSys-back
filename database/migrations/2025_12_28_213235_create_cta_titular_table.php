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
        Schema::create('cta_titular', function (Blueprint $table) {
            $table->unsignedBigInteger('cta_bancaria_id');
            $table->unsignedBigInteger('titular_id');
            $table->primary(['cta_bancaria_id', 'titular_id']);
            $table->foreign('cta_bancaria_id')->references('id')->on('cta_bancarias')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('titular_id')->references('id')->on('titulares')->onUpdate('cascade')->onDelete('restrict');
            $table->string('rol', 30)->default('TITULAR'); // TITULAR | COTITULAR | APODERADO | AUTORIZADO
            $table->boolean('firma_habilitada')->default(true);
            $table->string('estado', 20)->default('ACTIVO');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cta_titular');
    }
};
