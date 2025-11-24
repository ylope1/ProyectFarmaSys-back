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
        Schema::table('notas_comp_cab', function (Blueprint $table) {
            $table->timestamp('nota_comp_fec')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_comp_cab', function (Blueprint $table) {
            $table->date('nota_comp_fec')->change();
        });
    }
};
