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
         Schema::table('ajustes_cab', function (Blueprint $table) {
            $table->decimal('monto_exentas',14,2)->default(0);
            $table->decimal('monto_iva_5',14,2)->default(0);
            $table->decimal('monto_iva_10',14,2)->default(0);
            $table->decimal('monto_general',14,2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ajustes_cab', function (Blueprint $table) {
            $table->dropColumn([
                'monto_general',
                'monto_exentas',
                'monto_iva_5',
                'monto_iva_10'
            ]);
        });
    }
};
