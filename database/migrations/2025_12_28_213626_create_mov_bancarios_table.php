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
        Schema::create('mov_bancarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cta_bancaria_id');
            $table->unsignedBigInteger('titular_id');
            $table->foreign(['cta_bancaria_id', 'titular_id'])->references(['cta_bancaria_id', 'titular_id'])->on('cta_titular')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('mov_banc_fecha');
            $table->string('mov_banc_tipo', 20); // CHEQUE, TRANSFERENCIA, etc.
            // Datos específicos de cheque
            $table->string('mov_banc_nro_ref', 50)->nullable();
            $table->date('mov_banc_fec_emision')->nullable();
            $table->date('mov_banc_fec_valor')->nullable();//seria igual que fecha de pago
            $table->decimal('mov_banc_monto_debito', 15, 2)->default(0);
            $table->decimal('mov_banc_monto_credito', 15, 2)->default(0);
            $table->string('mov_banc_estado', 20)->default('REGISTRADO');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sucursal_id');
            $table->string('observacion', 255)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mov_bancarios');
    }
};
