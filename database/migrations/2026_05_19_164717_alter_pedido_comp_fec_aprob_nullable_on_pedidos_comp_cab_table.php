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
        Schema::table('pedidos_comp_cab', function (Blueprint $table) {
            $table->timestamp('pedido_comp_fec_aprob')
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos_comp_cab', function (Blueprint $table) {
            $table->timestamp('pedido_comp_fec_aprob')
                ->nullable(false)
                ->change();
        });
    }
};
