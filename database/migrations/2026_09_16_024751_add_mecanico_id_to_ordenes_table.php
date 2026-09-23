<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{ 
    public function up()
    {
        Schema::table('ordenes', function (Blueprint $table) {
            // Agregamos la columna y le decimos que puede estar vacía (nullable)
            $table->unsignedBigInteger('mecanico_id')->nullable(); 
        });
    }

    public function down()
    {
        Schema::table('ordenes', function (Blueprint $table) {
            $table->dropColumn('mecanico_id');
        });
    }
};