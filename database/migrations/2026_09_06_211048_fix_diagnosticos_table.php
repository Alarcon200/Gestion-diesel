<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('diagnosticos', function (Blueprint $table) {
            $table->foreignId('orden_id')->constrained('ordenes');
            $table->foreignId('usuario_id')->constrained('usuarios');
            $table->text('problema_encontrado');
            $table->text('causa_probable')->nullable();
            $table->text('trabajo_a_realizar');
            $table->text('recomendaciones')->nullable();
            $table->timestamp('fecha');
        });
    }
    public function down(): void {}
};