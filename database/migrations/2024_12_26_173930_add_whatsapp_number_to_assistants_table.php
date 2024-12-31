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
        Schema::table('assistants', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable(); // Agregar el campo de WhatsApp
        });
    }
    
    public function down()
    {
        Schema::table('assistants', function (Blueprint $table) {
            $table->dropColumn('whatsapp_number'); // Eliminar el campo si es necesario revertir la migración
        });
    }
    
};
