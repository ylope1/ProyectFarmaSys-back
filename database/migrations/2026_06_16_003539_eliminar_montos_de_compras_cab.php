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
            $table->dropColumn([
                'monto_exentas',
                'monto_grav_5',
                'monto_grav_10',
                'monto_iva_5',
                'monto_iva_10',
                'monto_general'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compras_cab', function (Blueprint $table) {

            $table->decimal('monto_exentas', 18, 2)->default(0);
            $table->decimal('monto_grav_5', 18, 2)->default(0);
            $table->decimal('monto_grav_10', 18, 2)->default(0);

            $table->decimal('monto_iva_5', 18, 2)->default(0);
            $table->decimal('monto_iva_10', 18, 2)->default(0);

            $table->decimal('monto_general', 18, 2)->default(0);
        });
    }
};
