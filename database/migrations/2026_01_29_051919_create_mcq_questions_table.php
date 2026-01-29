<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mcq_questions', function (Blueprint $table) {
            $table->id();

            // Foreign Keys (Relationships)
            // আপনার আগের টেবিল স্ট্রাকচার অনুযায়ী cascade delete দেওয়া হলো
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('chapter_id')->nullable();
            $table->unsignedBigInteger('topic_id')->nullable();

            // Question Basic Info
            $table->longText('question'); // প্রশ্ন বড় হতে পারে
            
            // 4 Options
            $table->longText('option_1');
            $table->longText('option_2');
            $table->longText('option_3');
            $table->longText('option_4');
            
            // Answer (1, 2, 3, 4 যেকোনো একটি হবে)
            $table->tinyInteger('answer')->comment('1=Option1, 2=Option2, 3=Option3, 4=Option4');

            // Others
            $table->text('tags')->nullable(); // কমা সেপারেটেড বা JSON হিসেবে রাখা যাবে
            $table->longText('short_description')->nullable();
            
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
        Schema::dropIfExists('mcq_questions');
    }
};