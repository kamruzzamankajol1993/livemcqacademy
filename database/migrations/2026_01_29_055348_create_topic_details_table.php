<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('topic_details', function (Blueprint $table) {
            $table->id();

            // Foreign Keys
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();
            $table->unsignedBigInteger('chapter_id')->nullable();
            $table->unsignedBigInteger('topic_id')->nullable();

            // Main Content
            $table->longText('topic_description')->nullable();
            
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            // Constraints (Foreign Key রিলেশন)
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('class_id')->references('id')->on('school_classes')->onDelete('cascade');
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('chapter_id')->references('id')->on('chapters')->onDelete('cascade');
            $table->foreign('topic_id')->references('id')->on('topics')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('topic_details');
    }
};