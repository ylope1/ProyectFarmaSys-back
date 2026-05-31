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
        Schema::rename('perfiles', 'roles');

        Schema::table('roles', function (Blueprint $table) {
            $table->renameColumn('perf_desc', 'rol_desc');
            $table->renameColumn('perf_abreviatura', 'rol_abreviatura');
            $table->string('rol_estado', 20)->default('ACTIVO');
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('roles', function (Blueprint $table) {
            $table->dropColumn('rol_estado');
            $table->renameColumn('rol_desc', 'perf_desc');
            $table->renameColumn('rol_abreviatura', 'perf_abreviatura');
        });
        Schema::rename('roles', 'perfiles');
    }
};
