<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mcq_questions', function (Blueprint $table) {
            // Adding new columns after 'topic_id'
            $table->unsignedBigInteger('institute_id')->nullable()->after('topic_id');
            $table->unsignedBigInteger('board_id')->nullable()->after('institute_id');
            $table->unsignedBigInteger('year_id')->nullable()->after('board_id'); // This refers to Academic Year

            // Adding Foreign Key Constraints
            $table->foreign('institute_id')->references('id')->on('institutes')->onDelete('set null');
            $table->foreign('board_id')->references('id')->on('boards')->onDelete('set null');
            $table->foreign('year_id')->references('id')->on('academic_years')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('mcq_questions', function (Blueprint $table) {
            // Dropping Foreign Keys first
            $table->dropForeign(['institute_id']);
            $table->dropForeign(['board_id']);
            $table->dropForeign(['year_id']);

            // Dropping Columns
            $table->dropColumn(['institute_id', 'board_id', 'year_id']);
        });
    }
};