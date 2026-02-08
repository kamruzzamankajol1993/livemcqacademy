<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->foreignId('class_id')->nullable()->constrained('school_classes')->onDelete('set null');
    });

    Schema::table('customers', function (Blueprint $table) {
        $table->foreignId('class_id')->nullable()->constrained('school_classes')->onDelete('set null');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users_and_customers', function (Blueprint $table) {
            //
        });
    }
};
