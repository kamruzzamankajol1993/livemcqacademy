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
        Schema::create('user_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained()->onDelete('cascade');
            
            // কোন পেমেন্টের মাধ্যমে এই সাবস্ক্রিপশন কেনা হয়েছে (লিংক রাখা ভালো)
            $table->foreignId('payment_id')->nullable()->constrained()->onDelete('set null');
            
            $table->dateTime('start_date');
            $table->dateTime('end_date'); // কবে মেয়াদ শেষ হবে
            
            // active = বর্তমানে চলছে, expired = মেয়াদ শেষ
            $table->enum('status', ['active', 'expired'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_subscriptions');
    }
};
