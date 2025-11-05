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
        Schema::table('orden_comp_cab', function (Blueprint $table) {
            $table->unsignedBigInteger('presup_comp_id')->nullable()->change();
            $table->unsignedBigInteger('pedido_comp_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orden_comp_cab', function (Blueprint $table) {
            $table->unsignedBigInteger('presup_comp_id')->nullable(false)->change();
            $table->unsignedBigInteger('pedido_comp_id')->nullable(false)->change();
        });
    }
};
