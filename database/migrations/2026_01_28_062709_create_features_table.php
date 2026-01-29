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
        Schema::create('features', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable(); // Parent ID
            $table->string('bangla_name');
            $table->string('english_name');
            $table->string('slug');
            $table->string('color')->nullable(); // Hex or RGB
            $table->string('image')->nullable(); // 80x80 pixel
            $table->text('short_description')->nullable();
            $table->tinyInteger('status')->default(1); // 1=Active, 0=Inactive
            $table->timestamps();

            // Foreign key constraint (optional, safe to keep)
            $table->foreign('parent_id')->references('id')->on('features')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('features');
    }
};
