<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('repuestos', function (Blueprint $table) {
            $table->string('nombre', 120);
            $table->text('descripcion')->nullable();
            $table->decimal('precio_referencia', 12, 2)->nullable();
            $table->string('unidad_medida', 20)->nullable();
        });
    }
    public function down(): void {}
};