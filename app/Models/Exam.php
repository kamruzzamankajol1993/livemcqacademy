<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    /**
     * যে ফিল্ডগুলো ম্যাস অ্যাসাইনমেন্টের মাধ্যমে সেভ করা যাবে।
     */
    protected $fillable = [
        'exam_category_ids',
        'negative_marks',
        'total_questions',
        'per_question_mark',
        'pass_mark',
        'exam_duration_minutes',
        'status'
    ];

    /**
     * ডাটাবেসের JSON কলামগুলোকে PHP অ্যারেতে কাস্ট করা।
     * এর ফলে কন্ট্রোলারে সরাসরি অ্যারে ইনপুট দেওয়া যাবে।
     */
    protected $casts = [
        'exam_category_ids' => 'array',
        'negative_marks' => 'array',
    ];

    // --- রিলেশনশিপ এবং এক্সেসর ---

    /**
     * মাল্টিপল ক্যাটাগরির নাম পাওয়ার জন্য একটি কাস্টম অ্যাট্রিবিউট।
     * এটি ইনডেক্স টেবিল বা শো পেজে নাম দেখানোর কাজ সহজ করবে।
     */
    public function getCategoryNamesAttribute()
    {
        if (empty($this->exam_category_ids)) {
            return [];
        }

        return ExamCategory::whereIn('id', $this->exam_category_ids)
            ->pluck('name_en')
            ->toArray();
    }

    /**
     * স্ট্যাটাস একটিভ আছে কি না তা চেক করার জন্য স্কোপ।
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }
}