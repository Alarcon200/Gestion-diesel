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
    Schema::create('asignaciones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('orden_id')->constrained('ordenes');
        $table->foreignId('usuario_id')->constrained('usuarios');
        $table->timestamp('fecha_asignacion');
        $table->string('estado', 30)->default('asignado');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones');
    }
};
