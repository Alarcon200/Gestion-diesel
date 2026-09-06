<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            $table->foreignId('orden_id')->nullable()->constrained('ordenes');
            $table->foreignId('mecanico_id')->constrained('usuarios');
            $table->text('trabajo_realizado');
            $table->text('observaciones')->nullable();
            $table->timestamp('fecha');
        });
    }

    public function down(): void
    {
        Schema::table('reparaciones', function (Blueprint $table) {
            //
        });
    }
};
