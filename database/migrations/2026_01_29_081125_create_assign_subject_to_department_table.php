<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assign_subject_to_department', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('class_department_id');
            $table->timestamps();

            // Foreign Keys
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('class_department_id')->references('id')->on('class_departments')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assign_subject_to_department');
    }
};