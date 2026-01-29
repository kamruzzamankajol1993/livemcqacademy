<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cq_questions', function (Blueprint $table) {
            $table->id();

            // Foreign Keys (Relationships)
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('chapter_id')->nullable();
            $table->unsignedBigInteger('topic_id')->nullable();

            // Main Content
            // উদ্দীপক (Stem): কবিতা, অনুচ্ছেদ বা মেইন নির্দেশনা এখানে থাকবে। 
            // Example 3 এর ক্ষেত্রে এটি Null হতে পারে।
            $table->longText('stem')->nullable(); 

            // Questions & Answers (JSON)
            // এখানে Array আকারে প্রশ্ন ও উত্তর থাকবে। 
            // যেমন: [{question: "প্রশ্ন ক", answer: "উত্তর..."}, {question: "প্রশ্ন খ", answer: "উত্তর..."}]
            $table->json('sub_questions'); 

            // Others
            $table->json('tags')->nullable(); // ট্যাগ JSON অ্যারে হিসেবে থাকবে
            $table->longText('short_description')->nullable(); // অতিরিক্ত নোট বা হিন্টস
            
            // Counters
            $table->bigInteger('view_count')->default(0);
            $table->bigInteger('total_likes')->default(0);
            $table->bigInteger('total_dislikes')->default(0);

            // Upload Type (Default: Subject Wise)
            $table->string('upload_type')->default('subject_wise'); 
            
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            // Constraints
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('class_id')->references('id')->on('school_classes')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('cascade');
            $table->foreign('topic_id')->references('id')->on('topics')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cq_questions');
    }
};