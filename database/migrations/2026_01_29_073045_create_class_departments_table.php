<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Class Departments Table
        Schema::create('class_departments', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_bn');
            $table->string('slug')->unique();
            $table->string('color')->nullable();
            $table->string('icon')->nullable(); // Image Path
            $table->integer('serial')->default(0);
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        // 2. Assign Class to Department (Pivot Table)
        Schema::create('assign_class_to_department', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('class_department_id');
            $table->unsignedBigInteger('class_id'); // School Class ID
            $table->timestamps();

            // Foreign Keys
            $table->foreign('class_department_id')->references('id')->on('class_departments')->onDelete('cascade');
            $table->foreign('class_id')->references('id')->on('school_classes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assign_class_to_department');
        Schema::dropIfExists('class_departments');
    }
};