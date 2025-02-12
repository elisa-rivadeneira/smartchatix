<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('users_english', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // Relación con la tabla users
            $table->string('level')->nullable(); // Nivel de inglés (Beginner, Intermediate, Advanced)
            $table->text('progress')->nullable(); // Resumen del progreso del usuario
            $table->json('history')->nullable(); // Historial de conversaciones o errores comunes
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('users_english');
    }
};
