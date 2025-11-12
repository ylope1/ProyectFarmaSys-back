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
        Schema::create('notas_comp_det', function (Blueprint $table) {
            $table->unsignedBigInteger('nota_comp_id');
            $table->unsignedBigInteger('producto_id');
            $table->primary(['nota_comp_id','producto_id']);
            $table->foreign('nota_comp_id')->references('id')->on('notas_comp_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict')->onUpdate('cascade');
            $table->integer('compra_cant');
            $table->double('compra_costo');
            $table->string('nota_comp_motivo');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notas_comp_det');
    }
};
