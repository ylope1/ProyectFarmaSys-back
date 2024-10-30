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
        Schema::create('presup_comp_det', function (Blueprint $table) {
            $table->unsignedBigInteger('presup_comp_id');
            $table->unsignedBigInteger('producto_id');
            $table->foreign('presup_comp_id')->references('id')->on('presup_comp_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict')->onUpdate('cascade');
            $table->double('presup_comp_cant');
            $table->integer('presup_comp_costo');
            $table->unsignedBigInteger('stock_id');
            $table->foreign('stock_id')->references('id')->on('stock')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('deposito_id');
            $table->foreign('deposito_id')->references('id')->on('depositos')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presup_comp_det');
    }
};
