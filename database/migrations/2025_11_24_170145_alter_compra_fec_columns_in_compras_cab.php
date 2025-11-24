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
        Schema::table('compras_cab', function (Blueprint $table) {
            $table->timestamp('compra_fec')->change();
            $table->timestamp('compra_fec_recep')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compras_cab', function (Blueprint $table) {
            $table->date('compra_fec')->change();
            $table->date('compra_fec_recep')->change();
        });
    }
};
