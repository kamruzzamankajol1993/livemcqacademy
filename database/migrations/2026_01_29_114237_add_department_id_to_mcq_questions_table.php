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
        Schema::table('mcq_questions', function (Blueprint $table) {
            // Adding class_department_id (Use this name to match class_departments table)
            $table->unsignedBigInteger('class_department_id')->nullable()->after('class_id');

            // Foreign Key Constraint
            $table->foreign('class_department_id')->references('id')->on('class_departments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('mcq_questions', function (Blueprint $table) {
            $table->dropForeign(['class_department_id']);
            $table->dropColumn('class_department_id');
        });
    }
};
