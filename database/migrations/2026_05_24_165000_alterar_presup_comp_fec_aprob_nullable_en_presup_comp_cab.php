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
        Schema::table('presup_comp_cab', function (Blueprint $table) {
            $table->timestamp('presup_comp_fec_aprob')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('presup_comp_cab', function (Blueprint $table) {
            $table->timestamp('presup_comp_fec_aprob')->nullable(false)->change();
        });
    }
};
