<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->unsignedBigInteger('category_id')->nullable();
            $table->unsignedBigInteger('class_id')->nullable();
            $table->unsignedBigInteger('subject_id')->nullable();

            // Columns
            $table->string('name_en');
            $table->string('name_bn');
            $table->string('slug')->unique();
            $table->tinyInteger('status')->default(1);
            
            $table->timestamps();

            // Constraints
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
            $table->foreign('class_id')->references('id')->on('school_classes')->onDelete('cascade'); // Table name 'school_classes'
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sections');
    }
};