<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Subjects Table
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable(); // For Parent Subject
            $table->string('name_en');
            $table->string('name_bn');
            $table->string('slug')->unique();
            $table->string('color')->nullable();
            $table->string('icon')->nullable(); // Image
            $table->integer('serial')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();

            $table->foreign('parent_id')->references('id')->on('subjects')->onDelete('set null');
        });

        // 2. Pivot Table: Subject has multiple Classes
        Schema::create('assign_class_to_subject', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('class_id'); // References school_classes table
            $table->timestamps();

            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('school_classes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assign_class_to_subject');
        Schema::dropIfExists('subjects');
    }
};