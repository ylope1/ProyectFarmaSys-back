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
            $table->renameColumn('total_exentas', 'monto_exentas');
            $table->renameColumn('total_grav_5', 'monto_grav_5');
            $table->renameColumn('total_grav_10', 'monto_grav_10');
            $table->renameColumn('total_iva_5', 'monto_iva_5');
            $table->renameColumn('total_iva_10', 'monto_iva_10');
            $table->renameColumn('total_general', 'monto_general');
        
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compras_cab', function (Blueprint $table) {
            $table->renameColumn('monto_exentas', 'total_exentas');
            $table->renameColumn('monto_grav_5', 'total_grav_5');
            $table->renameColumn('monto_grav_10', 'total_grav_10');
            $table->renameColumn('monto_iva_5', 'total_iva_5');
            $table->renameColumn('monto_iva_10', 'total_iva_10');
            $table->renameColumn('monto_general', 'total_general');
        });
    }
};
