<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->foreignId('orden_id')->constrained('ordenes');
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->decimal('subtotal_mano_obra', 12, 2)->default(0);
            $table->decimal('subtotal_repuestos', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->string('estado', 20)->default('pendiente');
            $table->timestamp('fecha_creacion');
            $table->timestamp('fecha_respuesta')->nullable();
        });
    }
    public function down(): void {}
};