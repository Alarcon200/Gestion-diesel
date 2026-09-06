<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fotografias', function (Blueprint $table) {
            $table->unsignedBigInteger('fotografiable_id');
            $table->string('fotografiable_type', 60);
            $table->string('ruta_archivo', 255);
            $table->string('descripcion', 200)->nullable();
            $table->timestamp('fecha_subida');
            $table->index(['fotografiable_id', 'fotografiable_type']);
        });
    }
    public function down(): void {}
};