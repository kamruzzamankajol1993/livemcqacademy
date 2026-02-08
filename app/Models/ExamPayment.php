<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'exam_package_id',
        'payment_method',
        'amount',
        'sender_number',
        'transaction_id',
        'expire_date',
        'status'
    ];

    // ইউজার রিলেশন
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // এক্সাম প্যাকেজ রিলেশন
    public function examPackage()
    {
        return $this->belongsTo(ExamPackage::class, 'exam_package_id');
    }
}