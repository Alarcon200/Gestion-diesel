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
    Schema::create('reparaciones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('orden_id')->constrained('ordenes');
        $table->foreignId('mecanico_id')->constrained('usuarios');
        $table->text('trabajo_realizado');
        $table->text('observaciones')->nullable();
        $table->timestamp('fecha');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reparaciones');
    }
};
