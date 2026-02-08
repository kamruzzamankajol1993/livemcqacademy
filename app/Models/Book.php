<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Book extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * Boot logic for Slug generation
     */
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($book) {
            $book->slug = static::createUniqueSlug($book->title);
        });
    }

    private static function createUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $count = static::where('slug', 'LIKE', "{$slug}%")->count();
        return $count ? "{$slug}-{$count}" : $slug;
    }

    // ক্যাটাগরির সাথে রিলেশন
    public function category()
    {
        return $this->belongsTo(BookCategory::class, 'book_category_id');
    }

    // মাল্টিপল ক্লাসের সাথে রিলেশন (Many-to-Many)
    public function schoolClasses()
    {
        return $this->belongsToMany(SchoolClass::class, 'book_school_class', 'book_id', 'school_class_id');
    }

    public function subject()
{
    return $this->belongsTo(Subject::class, 'subject_id');
}

public function reviews() {
    return $this->hasMany(BookReview::class)->where('status', 1);
}

// গড় রেটিং বের করার জন্য একটি অ্যাট্রিবিউট
public function getAverageRatingAttribute() {
    return round($this->reviews()->avg('rating'), 1) ?: 0;
}
}