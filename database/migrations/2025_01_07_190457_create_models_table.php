<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('models', function (Blueprint $table) {
            $table->id(); // ID único para cada modelo
            $table->string('name'); // Nombre amigable del modelo (ej: GPT-3.5 Turbo)
            $table->string('identifier')->unique(); // Identificador único (ej: gpt-3.5-turbo o ft:gpt-3.5-...)
            $table->text('description')->nullable(); // Descripción del modelo
            $table->timestamps();
        });
    }
    
    public function down()
    {
        Schema::dropIfExists('models');
    }
};
