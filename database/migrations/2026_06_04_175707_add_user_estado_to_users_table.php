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
        if (!Schema::hasColumn('users', 'user_estado')) {
        Schema::table('users', function (Blueprint $table) {
            $table->string('user_estado', 20)->default('ACTIVO');
            });
        }

        DB::table('users')
            ->whereNull('user_estado')
            ->update([
                'user_estado' => 'ACTIVO'
            ]);
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'user_estado')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('user_estado');
            });
        }
    }
};
