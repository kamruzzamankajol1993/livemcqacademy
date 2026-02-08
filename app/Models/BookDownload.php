<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookDownload extends Model
{
    use HasFactory;

    /**
     * যে কলামগুলোতে ডাটা ইনসার্ট করা যাবে।
     */
    protected $fillable = [
        'user_id',
        'book_id'
    ];

    /**
     * ইউজারের সাথে রিলেশন (কে ডাউনলোড করেছে)।
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * বইয়ের সাথে রিলেশন (কোন বইটি ডাউনলোড করা হয়েছে)।
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}