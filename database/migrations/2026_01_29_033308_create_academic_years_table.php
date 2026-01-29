<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('academic_years', function (Blueprint $table) {
            $table->id();
            $table->string('name_en');
            $table->string('name_bn');
            $table->string('slug')->unique();
            $table->integer('serial')->default(0); // ড্র্যাগ এন্ড ড্রপ সর্টিং
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_years');
    }
};