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
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['perfil_id']);
            $table->renameColumn('perfil_id', 'rol_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('rol_id')
                ->references('id')
                ->on('roles')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['rol_id']);
            $table->renameColumn('rol_id', 'perfil_id');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('perfil_id')
                ->references('id')
                ->on('perfiles')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }
};
