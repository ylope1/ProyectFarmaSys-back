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
        Schema::create('cobros_cheques', function (Blueprint $table) {
            /* PK / FK a cobros_det (compuesta) */
            $table->unsignedBigInteger('cobro_id');
            $table->unsignedBigInteger('cta_cobrar_id');
            $table->unsignedBigInteger('cta_cobrar_venta_id');

            /* Banco del cheque */
            $table->unsignedBigInteger('entidad_emisora_id');

            /* Datos del cheque */
            $table->string('nro_cheque', 50);
            $table->date('fecha_vto');

            /* Estado del cheque */
            $table->string('estado_cheque', 20)->default('REGISTRADO');
            $table->timestamps();

            /* Primary Key */
            $table->primary(['cobro_id','cta_cobrar_id','cta_cobrar_venta_id']);

            /* Foreign Keys */
            $table->foreign(['cobro_id','cta_cobrar_id','cta_cobrar_venta_id'])->references(['cobro_id','cta_cobrar_id','cta_cobrar_venta_id'])->on('cobros_det')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('entidad_emisora_id')->references('id')->on('entidades_emisoras')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobros_cheques');
    }
};
