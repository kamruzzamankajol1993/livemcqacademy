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
        Schema::create('self_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('board_id')->constrained('boards')->onDelete('cascade');
        $table->foreignId('class_id')->constrained('school_classes')->onDelete('cascade');
        $table->foreignId('subject_id')->constrained('subjects')->onDelete('cascade');
        
        // কাস্টম কনফিগারেশন ডাটা
        $table->integer('question_limit'); // ইউজার কতটি প্রশ্ন চায়
        $table->integer('time_duration'); // মিনিট হিসেবে সময়
        $table->decimal('negative_mark', 4, 2); // কাস্টম নেগেটিভ মার্ক (যেমন: ০.২৫, ০.৫০)
        $table->decimal('pass_mark', 5, 2); // পাস মার্ক (যেমন: ৪০.০০)
        $table->decimal('per_question_mark', 4, 2)->default(1.00);
        
        // রেজাল্ট সেভ করার জন্য (আলাদা টেবিল না করে এখানে রাখাই ভালো)
        $table->integer('correct_answers')->nullable();
        $table->integer('wrong_answers')->nullable();
        $table->decimal('earned_marks', 8, 2)->nullable();
        $table->enum('result_status', ['pending', 'passed', 'failed'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('self_tests');
    }
};
