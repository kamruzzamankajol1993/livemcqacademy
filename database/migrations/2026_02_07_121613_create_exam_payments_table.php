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
        Schema::create('exam_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('exam_package_id')->constrained('exam_packages')->onDelete('cascade');
        $table->string('payment_method'); // bkash, nagad, manual
        $table->decimal('amount', 10, 2);
        $table->string('sender_number')->nullable(); // শুধুমাত্র ম্যানুয়ালের জন্য
        $table->string('transaction_id')->nullable(); // শুধুমাত্র ম্যানুয়ালের জন্য
        $table->date('expire_date')->nullable(); // ভ্যালিডিটি চেক করার জন্য
        $table->enum('status', ['pending', 'completed', 'rejected'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_payments');
    }
};
