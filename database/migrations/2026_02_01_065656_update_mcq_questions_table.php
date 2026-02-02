<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mcq_questions', function (Blueprint $table) {
          
            

            // ২. নতুন কলামসমূহ যুক্ত করা হচ্ছে
            $table->unsignedBigInteger('section_id')->nullable()->after('class_department_id');
            
            // ইনস্টিটিউট এবং বোর্ড আইডি মাল্টিপল রাখার জন্য JSON টাইপ
            $table->json('institute_ids')->nullable()->after('section_id');
            $table->json('board_ids')->nullable()->after('institute_ids');

            // MCQ টাইপ (Text/Image) এবং ইমেজ কলামসমূহ
            $table->string('mcq_type')->default('text')->after('topic_id'); // text, image
            $table->string('question_img')->nullable()->after('question');
            $table->string('option_1_img')->nullable()->after('option_1');
            $table->string('option_2_img')->nullable()->after('option_2');
            $table->string('option_3_img')->nullable()->after('option_3');
            $table->string('option_4_img')->nullable()->after('option_4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mcq_questions', function (Blueprint $table) {
            $table->unsignedBigInteger('year_id')->nullable();
            $table->dropColumn([
                'section_id', 
                'institute_ids', 
                'board_ids', 
                'mcq_type', 
                'question_img', 
                'option_1_img', 
                'option_2_img', 
                'option_3_img', 
                'option_4_img'
            ]);
        });
    }
};