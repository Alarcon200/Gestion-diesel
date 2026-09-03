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
    Schema::create('fotografias', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('fotografiable_id');
        $table->string('fotografiable_type', 60);
        $table->string('ruta_archivo', 255);
        $table->string('descripcion', 200)->nullable();
        $table->timestamp('fecha_subida');
        $table->index(['fotografiable_id', 'fotografiable_type']);
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fotografias');
    }
};
