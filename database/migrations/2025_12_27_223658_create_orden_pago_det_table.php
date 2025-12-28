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
        Schema::create('orden_pago_det', function (Blueprint $table) {
            $table->unsignedBigInteger('orden_pago_id');
            $table->unsignedBigInteger('ctas_pagar_id');
            $table->unsignedBigInteger('compra_id');
            $table->primary(['orden_pago_id','ctas_pagar_id','compra_id']); // Clave primaria compuesta
            $table->foreign('orden_pago_id')->references('id')->on('orden_pago_cab')->onDelete('restrict')->onUpdate('cascade'); // Claves foraneas
            $table->foreign(['ctas_pagar_id', 'compra_id'])->references(['id', 'compra_id'])->on('ctas_pagar')->onDelete('restrict')->onUpdate('cascade'); // Claves foraneas
            $table->integer('op_cuota_nro');
            $table->bigInteger('op_monto_pagar');
            $table->bigInteger('op_saldo');
            $table->date('op_fecha_vto');
            $table->timestamps();
        });       
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orden_pago_det');
    }
};
