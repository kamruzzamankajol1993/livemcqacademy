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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_id')->constrained()->onDelete('cascade'); // কোন প্যাকেজের জন্য পেমেন্ট
            
            $table->string('trx_id')->unique(); // ইউনিক ট্রানজেকশন আইডি
            $table->decimal('amount', 10, 2); // কত টাকা পেমেন্ট করেছে
            $table->string('payment_method')->nullable(); // bkash, nagad, sslcommerz, stripe

            $table->string('transaction_id');
            
            // পেমেন্ট স্ট্যাটাস (pending, success, failed, canceled)
            $table->enum('status', ['pending', 'success', 'failed', 'canceled'])->default('pending');
            
            // গেটওয়ে থেকে আসা পুরো রেসপন্স সেভ রাখার জন্য (ডিবাগিং এর জন্য জরুরি)
            $table->json('payment_details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
