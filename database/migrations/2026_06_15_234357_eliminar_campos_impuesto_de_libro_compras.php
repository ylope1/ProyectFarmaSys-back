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
        Schema::table('libro_compras', function (Blueprint $table) {
            $table->dropColumn([
                'impuesto_id',
                'impuesto_desc'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('libro_compras', function (Blueprint $table) {
            $table->unsignedBigInteger('impuesto_id');
            $table->string('impuesto_desc', 50);
        });
    }
};
