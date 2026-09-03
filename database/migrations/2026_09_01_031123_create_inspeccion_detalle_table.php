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
    Schema::table('inspeccion_detalle', function (Blueprint $table) {
        $table->foreignId('inspeccion_id')->constrained('inspecciones');
        $table->string('item', 60);
        $table->string('estado', 30);
        $table->string('observacion', 200)->nullable();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('inspeccion_detalle', function (Blueprint $table) {
            //
        });
    }
};
