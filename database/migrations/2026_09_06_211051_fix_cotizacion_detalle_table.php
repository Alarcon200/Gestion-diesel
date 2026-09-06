<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizacion_detalle', function (Blueprint $table) {
            $table->foreignId('cotizacion_id')->constrained('cotizaciones');
            $table->foreignId('repuesto_id')->nullable()->constrained('repuestos');
            $table->string('tipo', 20);
            $table->string('descripcion', 200);
            $table->decimal('cantidad', 10, 2)->default(1);
            $table->decimal('precio_unitario', 12, 2);
            $table->decimal('subtotal', 12, 2);
        });
    }
    public function down(): void {}
};
