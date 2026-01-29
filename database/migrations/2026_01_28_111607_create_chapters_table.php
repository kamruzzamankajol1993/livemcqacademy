<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            
            // Foreign Keys
            $table->unsignedBigInteger('subject_id');
            $table->unsignedBigInteger('section_id')->nullable(); // Section Optional হতে পারে

            // Columns
            $table->string('name_en');
            $table->string('name_bn');
            $table->string('slug')->unique();
            $table->integer('serial')->default(0);
            $table->tinyInteger('status')->default(1);
            
            $table->timestamps();

            // Constraints
            $table->foreign('subject_id')->references('id')->on('subjects')->onDelete('cascade');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};