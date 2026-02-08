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
    Schema::table('exam_packages', function (Blueprint $table) {
        $table->foreignId('exam_category_id')->nullable()->after('id')->constrained('exam_categories')->onDelete('cascade');
    });
}

public function down(): void
{
    Schema::table('exam_packages', function (Blueprint $table) {
        $table->dropForeign(['exam_category_id']);
        $table->dropColumn('exam_category_id');
    });
}
};
