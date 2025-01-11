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
        Schema::table('document_trainings', function (Blueprint $table) {
            $table->string('file_id')->nullable();  // Añadir columna file_id
        });
    }
    
    public function down()
    {
        Schema::table('document_trainings', function (Blueprint $table) {
            $table->dropColumn('file_id');  // Eliminar columna si se revierte la migración
        });
    }
    


};
