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
        $table->string('facebook_id')->nullable();
        $table->string('google_id')->nullable(); // গুগলের জন্য আগেই রেখে দিলাম
        $table->string('avatar')->nullable();
        $table->string('password')->nullable()->change(); // পাসওয়ার্ড নাল হতে পারে
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
};
