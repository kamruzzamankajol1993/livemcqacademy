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
        Schema::table('exams', function (Blueprint $table) {
            // ১. প্রথমে ফরেন কি কনস্ট্রেইন্টটি ড্রপ করতে হবে
            // লারাভেলের ডিফল্ট ফরম্যাট অনুযায়ী ইনডেক্স নাম হলো: টেবিলনাম_কলামনাম_foreign
            if (Schema::hasColumn('exams', 'exam_category_id')) {
                $table->dropForeign(['exam_category_id']); 
                // অথবা $table->dropForeign('exams_exam_category_id_foreign');
                
                // ২. এখন কলামটি ড্রপ করা যাবে
                $table->dropColumn('exam_category_id');
            }

            if (Schema::hasColumn('exams', 'negative_mark')) {
                $table->dropColumn('negative_mark');
            }

            // ৩. মাল্টিপল ডাটা রাখার জন্য নতুন JSON কলাম যোগ করা
            $table->json('exam_category_ids')->after('id')->nullable();
            $table->json('negative_marks')->after('exam_category_ids')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exams', function (Blueprint $table) {
            $table->dropColumn(['exam_category_ids', 'negative_marks']);
            
            // রোলব্যাক লজিক
            $table->unsignedBigInteger('exam_category_id')->nullable();
            $table->float('negative_mark')->default(0);
            
            // ফরেন কি পুনরায় যোগ করা (যদি প্রয়োজন হয়)
            $table->foreign('exam_category_id')->references('id')->on('exam_categories')->onDelete('cascade');
        });
    }
};