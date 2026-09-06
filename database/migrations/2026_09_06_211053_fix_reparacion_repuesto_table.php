<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reparacion_repuesto', function (Blueprint $table) {
            $table->foreignId('reparacion_id')->constrained('reparaciones');
            $table->foreignId('repuesto_id')->constrained('repuestos');
            $table->decimal('cantidad', 10, 2)->default(1);
        });
    }
    public function down(): void {}
};