<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('books', function (Blueprint $table) {
            // subject_id কলাম যুক্ত করা এবং ফরেন কি সেট করা
            $table->foreignId('subject_id')
                  ->nullable() 
                  ->after('book_category_id') 
                  ->constrained('subjects') 
                  ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('books', function (Blueprint $table) {
            // রোলব্যাক করার সময় ফরেন কি এবং কলাম ড্রপ করা
            $table->dropForeign(['subject_id']);
            $table->dropColumn('subject_id');
        });
    }
};
