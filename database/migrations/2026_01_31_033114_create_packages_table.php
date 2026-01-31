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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // S-BASIC, S-PLUS
    $table->string('slug')->unique();
    $table->enum('type', ['monthly', 'yearly']);
    $table->decimal('original_price', 8, 2);
    $table->decimal('price', 8, 2);
    $table->boolean('is_popular')->default(false);
    $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
