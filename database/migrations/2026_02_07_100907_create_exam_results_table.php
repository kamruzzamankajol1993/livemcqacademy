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
        Schema::create('exam_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->foreignId('exam_package_id')->constrained()->onDelete('cascade');
        $table->json('submitted_answers'); // ইউজার যা যা উত্তর দিয়েছে [question_id => selected_option]
        $table->integer('total_questions');
        $table->integer('correct_answers')->default(0);
        $table->integer('wrong_answers')->default(0);
        $table->integer('skipped_questions')->default(0);
        $table->decimal('total_marks', 8, 2)->default(0.00);
        $table->decimal('earned_marks', 8, 2)->default(0.00);
        $table->string('result_status')->default('pending'); // pending (for free), published (for paid)
        $table->text('suggestion_text')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_results');
    }
};
