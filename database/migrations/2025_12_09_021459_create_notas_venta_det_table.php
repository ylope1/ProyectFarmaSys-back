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
        Schema::create('notas_venta_det', function (Blueprint $table) {
            $table->unsignedBigInteger('nota_venta_id');
            $table->unsignedBigInteger('producto_id');
            $table->primary(['nota_venta_id','producto_id']);
            $table->foreign('nota_venta_id')->references('id')->on('notas_venta_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict')->onUpdate('cascade');
            $table->integer('nota_venta_cant');
            $table->double('nota_venta_precio');
            $table->string('nota_venta_motivo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_venta_det');
    }
};
