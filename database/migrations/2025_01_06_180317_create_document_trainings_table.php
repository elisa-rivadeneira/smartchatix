<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentTrainingsTable extends Migration
{
    public function up()
    {
        Schema::create('document_trainings', function (Blueprint $table) {
            $table->id();
            $table->string('filename'); // Nombre del archivo
            $table->string('path'); // Ruta del archivo en el almacenamiento
            $table->foreignId('assistant_id')->constrained()->onDelete('cascade'); // Relación con el asistente
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('document_trainings');
    }
}
