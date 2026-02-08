<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamResult extends Model
{
    use HasFactory;

    /**
     * যে ফিল্ডগুলো ম্যাস অ্যাসাইনমেন্টের মাধ্যমে সেভ করা যাবে।
     *
     */
    protected $fillable = [
        'user_id',
        'exam_package_id',
        'submitted_answers',
        'total_questions',
        'correct_answers',
        'wrong_answers',
        'skipped_questions',
        'total_marks',
        'earned_marks',
        'result_status',
        'suggestion_text'
    ];

    /**
     * ডাটাবেসের নির্দিষ্ট কলামগুলোকে PHP ডাটা টাইপে রূপান্তর (Casting)।
     * submitted_answers-কে অ্যারে হিসেবে কাস্ট করা হয়েছে যাতে সরাসরি JSON সেভ ও রিড করা যায়।
     *
     */
    protected $casts = [
        'submitted_answers' => 'array',
        'total_marks' => 'decimal:2',
        'earned_marks' => 'decimal:2',
    ];

    // --- রিলেশনশিপসমূহ ---

    /**
     * এই রেজাল্টটি কোন ইউজারের তা জানার জন্য রিলেশন।
     *
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * এই রেজাল্টটি কোন এক্সাম প্যাকেজের আন্ডারে তার রিলেশন।
     *
     */
    public function examPackage()
    {
        return $this->belongsTo(ExamPackage::class, 'exam_package_id');
    }

    /**
     * স্ট্যাটাস পাবলিশড কি না তা চেক করার জন্য হেল্পার মেথড।
     */
    public function isPublished()
    {
        return $this->result_status === 'published';
    }
}