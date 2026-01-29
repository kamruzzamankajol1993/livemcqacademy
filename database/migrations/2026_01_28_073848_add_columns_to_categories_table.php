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
    Schema::table('categories', function (Blueprint $table) {
        $table->unsignedBigInteger('feature_id')->nullable()->after('id');
        $table->string('bangla_name')->nullable()->after('name');
        $table->string('english_name')->nullable()->after('bangla_name');
        $table->string('color')->nullable()->after('slug');
        $table->integer('serial')->default(0)->after('status');
        
        // Feature এর সাথে রিলেশন (অপশনাল, যদি feature টেবিল থাকে)
         $table->foreign('feature_id')->references('id')->on('features')->onDelete('set null'); 
    });
}

public function down()
{
    Schema::table('categories', function (Blueprint $table) {
        $table->dropColumn(['feature_id', 'bangla_name', 'english_name', 'color', 'serial']);
    });
}
};
