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
        Schema::create('courses', function (Blueprint $table) {
            $table->id(); // ID único del curso
            $table->string('title'); // Nombre del curso
            $table->text('description')->nullable(); // Descripción del curso
            $table->decimal('price', 10, 2)->default(0); // Precio del curso
            $table->integer('duration')->comment('Duración en horas'); // Duración del curso
            $table->string('category')->nullable(); // Categoría del curso
            $table->string('teacher'); // Nombre del profesor
            $table->text('description_teacher')->nullable(); // Descripción del profesor
            $table->timestamps(); // Campos created_at y updated_at
        });
    }
    

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
