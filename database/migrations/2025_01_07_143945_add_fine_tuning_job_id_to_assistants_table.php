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
            $table->string('fine_tuning_job_id')->nullable()->after('whatsapp_number');
        });
    }
    
    public function down()
    {
        Schema::table('assistants', function (Blueprint $table) {
            $table->dropColumn('fine_tuning_job_id');
        });
    }
    
};
