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
        Schema::create('facturas_varias_det', function (Blueprint $table) {
            $table->unsignedBigInteger('factura_varia_id');
            $table->unsignedBigInteger('rubro_id');
            // Clave primaria compuesta
            $table->primary(['factura_varia_id', 'rubro_id']);
            // Relaciones
            $table->foreign('factura_varia_id')->references('id')->on('facturas_varias_cab')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('rubro_id')->references('id')->on('rubros')->onDelete('restrict')->onUpdate('cascade');
            // Datos del detalle
            $table->integer('fact_var_cant');
            $table->double('fact_var_monto');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facturas_varias_det');
    }
};
