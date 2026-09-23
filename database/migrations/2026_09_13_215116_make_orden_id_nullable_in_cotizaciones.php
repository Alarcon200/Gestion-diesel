<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->dropForeign(['orden_id']);
        });

        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->foreignId('orden_id')->nullable()->change();
        });

        Schema::table('cotizaciones', function (Blueprint $table) {
            $table->foreign('orden_id')->references('id')->on('ordenes');
        });
    }

    public function down(): void
    {
        //
    }
};