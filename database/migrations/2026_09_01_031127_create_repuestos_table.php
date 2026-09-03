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
    Schema::create('solicitudes_repuestos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('orden_id')->constrained('ordenes');
        $table->foreignId('mecanico_id')->constrained('usuarios');
        $table->foreignId('mensajero_id')->nullable()->constrained('usuarios');
        $table->foreignId('repuesto_id')->constrained('repuestos');
        $table->decimal('cantidad', 10, 2)->default(1);
        $table->string('estado', 20)->default('solicitado');
        $table->timestamp('fecha_solicitud');
        $table->timestamp('fecha_entrega')->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repuestos');
    }
};
