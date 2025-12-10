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
        Schema::create('remision_vent_det', function (Blueprint $table) {
            $table->unsignedBigInteger('remision_vent_id');
            $table->unsignedBigInteger('producto_id');
            $table->primary(['remision_vent_id','producto_id']);
            $table->foreign('remision_vent_id')->references('id')->on('remision_vent_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict')->onUpdate('cascade');
            $table->integer('remision_vent_cant');
            $table->double('remision_vent_precio');
            $table->string('remision_vent_obs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remision_vent_det');
    }
};
