<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->string('numero_orden', 20)->unique();
            $table->foreignId('ingreso_id')->constrained('ingresos');
            $table->foreignId('vehiculo_id')->constrained('vehiculos');
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->timestamp('fecha_creacion');
            $table->string('estado', 30)->default('pendiente');
            $table->timestamp('fecha_finalizacion')->nullable();
        });
    }
    public function down(): void {}
};