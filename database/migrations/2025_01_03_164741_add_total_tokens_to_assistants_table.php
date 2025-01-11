<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTotalTokensToAssistantsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assistants', function (Blueprint $table) {
            // Añadir el campo total_tokens
            $table->unsignedBigInteger('total_tokens_used')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assistants', function (Blueprint $table) {
            // Eliminar el campo si revertimos la migración
            $table->dropColumn('total_tokens_used');
        });
    }
}
