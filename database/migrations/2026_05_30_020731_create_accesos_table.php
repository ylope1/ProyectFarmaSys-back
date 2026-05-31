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
        Schema::create('accesos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('modulo_id');
            $table->string('acc_desc', 100);
            $table->string('acc_ruta', 255);
            $table->integer('acc_orden')->default(0);
            $table->string('acc_estado', 20)->default('ACTIVO');
            $table->timestamps();

            $table->foreign('modulo_id')->references('id')->on('modulos')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accesos');
    }
};
