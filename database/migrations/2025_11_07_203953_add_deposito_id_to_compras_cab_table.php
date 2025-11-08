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
            $table->unsignedBigInteger('deposito_id')->default(1)->after('sucursal_id');

            $table->foreign('deposito_id')
                ->references('id')->on('depositos')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compras_cab', function (Blueprint $table) {
            $table->dropForeign(['deposito_id']);
            $table->dropColumn('deposito_id');
        });
    }
};
