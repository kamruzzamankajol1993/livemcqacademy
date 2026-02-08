<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookReview extends Model
{
    use HasFactory;

    /**
     * যে কলামগুলোতে ডাটা ইনসার্ট করা যাবে।
     *
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'rating',
        'comment',
        'status'
    ];

    /**
     * ইউজারের সাথে রিলেশন (বুক রিভিউটি কোন ইউজার দিয়েছে)।
     *
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * বইয়ের সাথে রিলেশন (রিভিউটি কোন বইয়ের জন্য)।
     *
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    /**
     * শুধুমাত্র একটিভ (status = 1) রিভিউগুলো ফিল্টার করার জন্য একটি স্কোপ।
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}