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
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('persona_id');
            $table->foreign('persona_id')->references('id')->on('personas')->onDelete('restrict')->onUpdate('cascade');
            $table->timestamp('cli_fec_nac');
            $table->timestamp('cli_fec_baja')->nullable();
            $table->timestamp('cli_fec_ing');
            $table->string('cli_estado', 20);
            $table->string('cli_ruc', 20)->nullable();
            $table->decimal('cli_linea_credito',14,2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clientes');
    }
};
