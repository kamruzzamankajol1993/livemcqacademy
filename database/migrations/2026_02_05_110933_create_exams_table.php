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
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            // ক্যাটাগরি আইডি (যেমন: বিসিএস, ব্যাংক জব ইত্যাদি)
            $table->foreignId('exam_category_id')->constrained()->onDelete('cascade');
            
            // এক্সাম কনফিগারেশন ফিল্ডসমূহ
            $table->integer('total_questions')->default(0);
            $table->float('per_question_mark')->default(1.0);
            $table->float('negative_mark')->default(0.0); // ০.২৫, ০.৫০ ইত্যাদি
            $table->integer('pass_mark')->default(0);
            $table->integer('exam_duration_minutes')->default(0); // পরীক্ষার সময়কাল
            
            $table->tinyInteger('status')->default(1)->comment('1:Active, 0:Inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
