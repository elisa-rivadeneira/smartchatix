<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToAssistantsTable extends Migration
{
    public function up()
    {
        Schema::table('assistants', function (Blueprint $table) {
            $table->string('type')->nullable(); // Agregar el campo 'type'
        });
    }

    public function down()
    {
        Schema::table('assistants', function (Blueprint $table) {
            $table->dropColumn('type'); // Eliminar el campo 'type'
        });
    }
}
