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
    Schema::table('otps', function (Blueprint $table) {
        $table->json('payload')->nullable()->after('otp'); // ডাটা হোল্ড করার জন্য
    });
}

public function down()
{
    Schema::table('otps', function (Blueprint $table) {
        $table->dropColumn('payload');
    });
}
};
