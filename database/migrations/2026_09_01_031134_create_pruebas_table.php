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
    Schema::create('pruebas', function (Blueprint $table) {
        $table->id();
        $table->foreignId('orden_id')->constrained('ordenes');
        $table->foreignId('usuario_id')->constrained('usuarios');
        $table->string('prueba_realizada', 200);
        $table->string('resultado', 30);
        $table->text('observaciones')->nullable();
        $table->timestamp('fecha');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pruebas');
    }
};
