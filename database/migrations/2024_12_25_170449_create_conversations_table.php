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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assistant_id'); // Agregar el campo para la relación con Assistant
            $table->foreign('assistant_id')->references('id')->on('assistants'); // Establecer la clave foránea
            $table->string('session_id', 255)->unique(); // Campo para almacenar el session_id con un índice único
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
