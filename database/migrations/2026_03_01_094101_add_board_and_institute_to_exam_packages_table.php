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
            $table->json('board_ids')->nullable()->after('exam_category_id');
        $table->json('institute_ids')->nullable()->after('board_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('exam_packages', function (Blueprint $table) {
            //
        });
    }
};
