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
        Schema::table('courses', function (Blueprint $table) {
            $table->string('modalidad')->nullable(); // Presencial, Virtual, Mixto
            $table->string('imagen')->nullable(); // Ruta o URL de la imagen
        });
    }
    
    public function down()
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['modalidad', 'imagen']);
        });
    }
    


};
