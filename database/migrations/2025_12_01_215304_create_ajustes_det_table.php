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
        Schema::create('ajustes_det', function (Blueprint $table) {
            $table->unsignedBigInteger('ajuste_id');
            $table->unsignedBigInteger('producto_id');
            $table->primary(['ajuste_id','producto_id']);
            $table->foreign('ajuste_id')->references('id')->on('ajustes_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict')->onUpdate('cascade');
            $table->integer('ajuste_cant');
            $table->double('ajuste_costo');
            $table->unsignedBigInteger('item_id');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ajustes_det');
    }
};
