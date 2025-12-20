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
        Schema::create('cobros_tarjetas', function (Blueprint $table) {
            /* PK / FK a cobros_det (compuesta) */
            $table->unsignedBigInteger('cobro_id');
            $table->unsignedBigInteger('cta_cobrar_id');
            $table->unsignedBigInteger('cta_cobrar_venta_id');

            /* Relación con catálogo de combinaciones válidas */
            $table->unsignedBigInteger('entidad_adherida_tarjeta_id');

            /* Datos propios de la tarjeta */
            $table->string('nro_tarjeta', 25);
            $table->string('fecha_vto', 7)->nullable(); // MM/YYYY

            /* Estado */
            $table->string('estado_tarjeta', 20); //registrado, confirmado, anulado

            $table->timestamps();

            /* Primary Key */
            $table->primary(['cobro_id','cta_cobrar_id','cta_cobrar_venta_id']);

            /* Foreign Keys */
            $table->foreign(['cobro_id','cta_cobrar_id','cta_cobrar_venta_id'])->references(['cobro_id','cta_cobrar_id','cta_cobrar_venta_id'])->on('cobros_det')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('entidad_adherida_tarjeta_id')->references('id')->on('entidades_adheridas_tarjetas')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobros_tarjetas');
    }
};
