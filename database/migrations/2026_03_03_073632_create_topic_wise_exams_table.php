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
        Schema::create('topic_wise_exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('subject_id')->constrained();
    $table->integer('chapter_id')->nullable();
    $table->integer('topic_id')->nullable();
    $table->integer('board_id')->nullable();
    $table->integer('institute_id')->nullable();
    $table->integer('question_limit');
    $table->integer('exam_duration'); // মিনিটে
    $table->json('questions_data'); // প্রশ্ন এবং উত্তরসহ সম্পূর্ণ ডাটা
    $table->json('user_answers')->nullable(); // ইউজারের দেওয়া উত্তর
    $table->decimal('earned_marks', 8, 2)->default(0);
    $table->integer('correct_answers')->default(0);
    $table->integer('wrong_answers')->default(0);
    $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('topic_wise_exams');
    }
};
