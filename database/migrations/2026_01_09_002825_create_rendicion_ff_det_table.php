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
        Schema::create('rendicion_ff_det', function (Blueprint $table) {
            $table->unsignedBigInteger('rendicion_ff_id');
            $table->unsignedBigInteger('documento_id');
            $table->primary(['rendicion_ff_id', 'documento_id']);
            $table->foreign('rendicion_ff_id')->references('id')->on('rendicion_ff_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('documento_id')->references('id')->on('documentos')->onDelete('restrict')->onUpdate('cascade');
            $table->bigInteger('rendicion_ff_det_monto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rendicion_ff_det');
    }
};
