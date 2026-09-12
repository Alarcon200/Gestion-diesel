<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes_repuestos', function (Blueprint $table) {
            $table->decimal('precio_compra', 12, 2)->nullable();
            $table->string('lugar_compra', 100)->nullable();
        });
    }
    public function down(): void {}
};
