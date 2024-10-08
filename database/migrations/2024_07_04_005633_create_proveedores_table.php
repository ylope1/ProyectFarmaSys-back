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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('proveedor_desc',25);
            $table->string('proveedor_ruc',25);
            $table->string('proveedor_tipo',15);
            $table->string('proveedor_direc',35);
            $table->string('proveedor_telef',15);
            $table->string('proveedor_email',25);
            $table->unsignedBigInteger('pais_id');
            $table->foreign('pais_id')->references('id')->on('paises')->onDelete('restrict')->onUpdate('cascade');
            $table->unsignedBigInteger('ciudad_id');
            $table->foreign('ciudad_id')->references('id')->on('ciudades')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proveedores');
    }
};
