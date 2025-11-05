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
        Schema::create('orden_comp_det', function (Blueprint $table) {
            $table->unsignedBigInteger('orden_comp_id');
            $table->unsignedBigInteger('producto_id');
            $table->foreign('orden_comp_id')->references('id')->on('orden_comp_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict')->onUpdate('cascade');
            $table->double('orden_comp_cant');
            $table->integer('orden_comp_costo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_comp_det');
    }
};
