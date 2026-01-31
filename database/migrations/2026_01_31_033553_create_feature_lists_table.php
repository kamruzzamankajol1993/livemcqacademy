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
        Schema::create('feature_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Free Model Test"
    $table->string('code')->unique(); // e.g., "free_model_test" (Dev এর জন্য)
    $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_lists');
    }
};
