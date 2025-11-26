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
        Schema::create('remision_comp_det', function (Blueprint $table) {
            $table->unsignedBigInteger('remision_comp_id');
            $table->unsignedBigInteger('producto_id');
            $table->primary(['remision_comp_id','producto_id']);
            $table->foreign('remision_comp_id')->references('id')->on('remision_comp_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict')->onUpdate('cascade');
            $table->integer('rem_comp_cant');
            $table->double('rem_comp_costo');
            $table->string('rem_comp_obs')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remision_comp_det');
    }
};
