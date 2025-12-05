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
        Schema::create('pedidos_vent_det', function (Blueprint $table) {
            $table->unsignedBigInteger('pedido_vent_id');
            $table->foreign('pedido_vent_id')->references('id')->on('pedidos_vent_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('producto_id');
            $table->foreign('producto_id')->references('id')->on('productos')->onDelete('restrict')->onUpdate('cascade');
            $table->primary(['pedido_vent_id','producto_id']);
            $table->integer('pedido_vent_cant');
            $table->decimal('pedido_vent_precio');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos_vent_det');
    }
};
