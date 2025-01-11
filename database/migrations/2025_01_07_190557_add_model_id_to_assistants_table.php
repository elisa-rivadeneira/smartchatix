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
            $table->unsignedBigInteger('model_id')->nullable(); // Relación con la tabla models
            $table->foreign('model_id')->references('id')->on('models')->onDelete('set null');
        });
    }
    
    public function down()
    {
        Schema::table('assistants', function (Blueprint $table) {
            $table->dropForeign(['model_id']);
            $table->dropColumn('model_id');
        });
    }
    
};
