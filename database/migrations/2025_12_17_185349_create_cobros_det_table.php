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
        Schema::create('cobros_det', function (Blueprint $table) {
            $table->unsignedBigInteger('cobro_id');
            $table->unsignedBigInteger('cta_cobrar_id');
            $table->unsignedBigInteger('cta_cobrar_venta_id');
            $table->unsignedBigInteger('forma_cobro_id');

            $table->decimal('monto_cobro', 15, 2);

            $table->timestamps();

            /* Primary Key Compuesta */
            $table->primary(['cobro_id', 'cta_cobrar_id','cta_cobrar_venta_id']);

            /* Foreign Keys */
            $table->foreign('cobro_id')->references('id')->on('cobros_cab')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign(['cta_cobrar_id', 'cta_cobrar_venta_id'])->references(['id', 'venta_id'])->on('ctas_cobrar')->onUpdate('cascade')->onDelete('restrict');
            $table->foreign('forma_cobro_id')->references('id')->on('forma_cobros')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cobros_det');
    }
};
