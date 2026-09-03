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
    Schema::create('ingresos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('vehiculo_id')->constrained('vehiculos');
        $table->foreignId('usuario_id')->constrained('usuarios');
        $table->string('conductor', 120)->nullable();
        $table->timestamp('fecha_ingreso');
        $table->string('motivo', 200);
        $table->integer('kilometraje')->nullable();
        $table->string('estado', 30)->default('en_taller');
        $table->text('observaciones')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingresos');
    }
};
