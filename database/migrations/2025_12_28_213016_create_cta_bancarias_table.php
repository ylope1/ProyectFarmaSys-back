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
        Schema::create('cta_bancarias', function (Blueprint $table) {
            $table->id();
            $table->string('cta_banc_nro_cuenta', 50);
            $table->string('cta_banc_banco', 100);
            $table->string('cta_banc_tipo', 20);    // CTA_CTE, CAJA_AHORRO
            $table->string('cta_banc_moneda', 10);  // PYG, USD
            $table->string('cta_banc_estado', 20)->default('ACTIVO');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cta_bancarias');
    }
};
