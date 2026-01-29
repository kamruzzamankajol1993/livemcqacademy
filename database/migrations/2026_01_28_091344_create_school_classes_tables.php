<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Classes Table
        Schema::create('school_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name_bn');
            $table->string('name_en');
            $table->string('slug');
            $table->string('image')->nullable();
            $table->string('color')->nullable();
            $table->integer('serial')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        // 2. Assign Category to Class Table (Pivot Table)
        Schema::create('assign_category_to_class', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_id');
            $table->unsignedBigInteger('category_id');
            $table->timestamps();

            // Foreign Keys (Optional but recommended)
            $table->foreign('class_id')->references('id')->on('school_classes')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assign_category_to_class');
        Schema::dropIfExists('school_classes');
    }
};