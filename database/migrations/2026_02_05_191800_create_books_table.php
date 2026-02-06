<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // মেইন বুক টেবিল
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique(); // SEO Friendly URL
            $table->string('isbn_code')->nullable(); // ISBN/Book Code
            $table->string('edition')->nullable(); // Edition (e.g., 2025)
            $table->string('image')->nullable();
            $table->string('language')->default('Bangla');
            $table->string('authority')->nullable();
            $table->integer('total_download')->default(0);
            $table->date('publish_date')->nullable();
            $table->text('short_description')->nullable();
            $table->string('preview_pdf')->nullable(); 
            $table->string('full_pdf')->nullable();    
            $table->enum('type', ['free', 'paid'])->default('free');
            $table->double('price', 10, 2)->default(0);
            $table->double('discount_price', 10, 2)->default(0);
            $table->foreignId('book_category_id')->constrained('book_categories')->onDelete('cascade');
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });

        // মাল্টিপল ক্লাসের জন্য পিভট টেবিল (Many-to-Many)
        Schema::create('book_school_class', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->foreignId('school_class_id')->constrained('school_classes')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('book_school_class');
        Schema::dropIfExists('books');
    }
};
