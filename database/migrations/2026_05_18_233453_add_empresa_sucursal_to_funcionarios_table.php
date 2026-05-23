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
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('sucursal_id')->nullable();

            $table->foreign('empresa_id')
                ->references('id')
                ->on('empresas')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('sucursal_id')
                ->references('id')
                ->on('sucursales')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('funcionarios', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropForeign(['sucursal_id']);

            $table->dropColumn('empresa_id');
            $table->dropColumn('sucursal_id');
        });
    }
};
