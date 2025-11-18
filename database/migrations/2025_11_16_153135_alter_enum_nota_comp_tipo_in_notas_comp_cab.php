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
            DB::statement("ALTER TABLE notas_comp_cab DROP COLUMN nota_comp_tipo");
            DB::statement("CREATE TYPE nota_tipo_enum AS ENUM ('NC', 'ND')");
            DB::statement("ALTER TABLE notas_comp_cab ADD COLUMN nota_comp_tipo nota_tipo_enum");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notas_comp_cab', function (Blueprint $table) {
            DB::statement("ALTER TABLE notas_comp_cab DROP COLUMN nota_comp_tipo");
            DB::statement("DROP TYPE IF EXISTS nota_tipo_enum");

            // Restaurar el ENUM anterior si es necesario
            DB::statement("CREATE TYPE nota_tipo_enum_old AS ENUM ('CREDITO', 'DEBITO')");
            DB::statement("ALTER TABLE notas_comp_cab ADD COLUMN nota_comp_tipo nota_tipo_enum_old");
        });
    }
};
