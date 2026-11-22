<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Guarda quién tomó cada foto (sirve como evidencia cuando hay un reclamo)
    public function up(): void
    {
        if (!Schema::hasColumn('fotografias', 'usuario_id')) {
            Schema::table('fotografias', function (Blueprint $table) {
                $table->unsignedBigInteger('usuario_id')->nullable();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('fotografias', 'usuario_id')) {
            Schema::table('fotografias', function (Blueprint $table) {
                $table->dropColumn('usuario_id');
            });
        }
    }
};
