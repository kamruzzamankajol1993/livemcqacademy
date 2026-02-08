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
    Schema::table('self_tests', function (Blueprint $table) {
        // ইউজারের দেওয়া সব উত্তর JSON হিসেবে সেভ করার জন্য
        $table->json('user_responses')->nullable()->after('subject_id');
    });
}

public function down()
{
    Schema::table('self_tests', function (Blueprint $table) {
        $table->dropColumn('user_responses');
    });
}
};
