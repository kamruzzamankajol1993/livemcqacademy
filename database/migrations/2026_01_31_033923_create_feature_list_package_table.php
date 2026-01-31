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
        Schema::create('feature_list_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            
            // feature_id এর বদলে feature_list_id
            $table->foreignId('feature_list_id')->constrained('feature_lists')->onDelete('cascade');
            
            $table->string('value')->nullable(); // Limit/Value (e.g. 10, Unlimited, Yes)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('feature_list_package');
    }
};
