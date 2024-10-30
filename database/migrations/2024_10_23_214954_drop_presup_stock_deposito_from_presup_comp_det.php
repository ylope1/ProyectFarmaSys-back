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
        Schema::table('presup_comp_det', function (Blueprint $table) {
            $table->dropColumn(['stock_id', 'deposito_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presup_comp_det', function (Blueprint $table) {
            $table->unsignedBigInteger('stock_id')->nullable();
            $table->unsignedBigInteger('deposito_id')->nullable();
        });
    }
};
