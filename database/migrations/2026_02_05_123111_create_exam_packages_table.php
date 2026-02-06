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
        Schema::create('exam_packages', function (Blueprint $table) {
            $table->id();
            
            // সিলেকশন ফিল্ডসমূহ
            $table->foreignId('class_id')->constrained('school_classes')->onDelete('cascade');
            $table->foreignId('class_department_id')->nullable()->constrained('class_departments')->onDelete('cascade');
            
            // মাল্টিপল সিলেকশনের জন্য JSON কলাম (সাবজেক্ট, চ্যাপ্টার, টপিক)
            $table->json('subject_ids')->nullable(); 
            $table->json('chapter_ids')->nullable();
            $table->json('topic_ids')->nullable();

            // এক্সাম ইনফরমেশন
            $table->string('exam_name');
            $table->enum('exam_type', ['free', 'paid'])->default('free');
            $table->decimal('price', 10, 2)->default(0.00);
            
            // ডিউরেশন (২, ৩ বা ৪ দিন)
            $table->integer('validity_days')->default(0)->comment('Exam availability duration in days');
            
            $table->tinyInteger('status')->default(1)->comment('1:Active, 0:Inactive');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_packages');
    }
};