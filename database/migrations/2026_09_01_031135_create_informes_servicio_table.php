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
    Schema::create('informes_servicio', function (Blueprint $table) {
        $table->id();
        $table->foreignId('orden_id')->constrained('ordenes');
        $table->foreignId('usuario_id')->constrained('usuarios');
        $table->decimal('valor_servicio', 12, 2)->default(0);
        $table->text('observaciones_finales')->nullable();
        $table->timestamp('fecha_generacion');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informes_servicio');
    }
};
