<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BookPayment extends Model
{
    use HasFactory;

    /**
     * যে কলামগুলোতে ডাটা ইনসার্ট করা যাবে।
     */
    protected $fillable = [
        'user_id',
        'book_id',
        'payment_method',
        'amount',
        'sender_number',
        'transaction_id',
        'status'
    ];

    /**
     * ইউজারের সাথে রিলেশন (কে পেমেন্ট করেছে)।
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * বইয়ের সাথে রিলেশন (কোন বইটির জন্য পেমেন্ট)।
     */
    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}