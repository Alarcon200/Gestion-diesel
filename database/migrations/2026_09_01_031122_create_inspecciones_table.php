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
    Schema::create('inspecciones', function (Blueprint $table) {
        $table->id();
        $table->foreignId('ingreso_id')->constrained('ingresos');
        $table->foreignId('usuario_id')->constrained('usuarios');
        $table->timestamp('fecha_inspeccion');
        $table->string('estado_general', 50)->nullable();
        $table->string('nivel_combustible', 20)->nullable();
        $table->integer('kilometraje')->nullable();
        $table->text('observaciones')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspecciones');
    }
};
