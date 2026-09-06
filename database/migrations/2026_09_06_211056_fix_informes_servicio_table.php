<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('informes_servicio', function (Blueprint $table) {
            $table->foreignId('orden_id')->constrained('ordenes');
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->decimal('valor_servicio', 12, 2)->default(0);
            $table->text('observaciones_finales')->nullable();
            $table->timestamp('fecha_generacion');
        });
    }
    public function down(): void {}
};