<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('notificaciones')) {
            return;
        }

        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id();
            // Si se elimina al usuario, se eliminan sus notificaciones
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->string('tipo', 30);
            $table->string('titulo', 150);
            $table->string('mensaje', 300)->nullable();
            $table->unsignedBigInteger('ingreso_id')->nullable();
            $table->timestamp('leida_at')->nullable();
            $table->timestamps();

            $table->index(['usuario_id', 'leida_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
