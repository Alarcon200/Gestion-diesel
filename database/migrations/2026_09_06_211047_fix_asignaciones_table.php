<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asignaciones', function (Blueprint $table) {
            $table->foreignId('orden_id')->constrained('ordenes');
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->timestamp('fecha_asignacion');
            $table->string('estado', 30)->default('asignado');
        });
    }
    public function down(): void {}
};