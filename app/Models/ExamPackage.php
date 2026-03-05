<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExamPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_category_id',
        'class_id', 
        'class_department_id', 
        'subject_ids', 
        'chapter_ids', 
        'board_ids',       // নতুন
    'institute_ids',   // নতুন
        'topic_ids', 
        'exam_name', 
        'start_time',
        'end_time',
        'exam_type', 
        'price', 
        'validity_days', 
        'status'
    ];

    /**
     * JSON কলামগুলোকে অ্যারেতে রূপান্তর করা
     */
    protected $casts = [
        'start_time' => 'datetime',
    'end_time'   => 'datetime',
       'board_ids' => 'array',     // নতুন
    'institute_ids' => 'array', // নতুন
    'subject_ids' => 'array',
    'chapter_ids' => 'array',
    'topic_ids' => 'array',
    ];

    // --- রিলেশনশিপসমূহ ---

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function department()
    {
        return $this->belongsTo(ClassDepartment::class, 'class_department_id');
    }

    /**
     * মাল্টিপল সাবজেক্ট গেট করার জন্য
     */
    public function getSubjectsAttribute()
    {
        return Subject::whereIn('id', $this->subject_ids ?? [])->get();
    }

    /**
     * মাল্টিপল চ্যাপ্টার গেট করার জন্য
     */
    public function getChaptersAttribute()
    {
        return Chapter::whereIn('id', $this->chapter_ids ?? [])->get();
    }

    /**
     * মাল্টিপল টপিক গেট করার জন্য
     */
    public function getTopicsAttribute()
    {
        return Topic::whereIn('id', $this->topic_ids ?? [])->get();
    }

    public function category()
{
    return $this->belongsTo(ExamCategory::class, 'exam_category_id');
}

// রিলেশনশিপ অ্যাক্সেসর
public function getBoardsAttribute()
{
    return \App\Models\Board::whereIn('id', $this->board_ids ?? [])->get();
}

public function getInstitutesAttribute()
{
    return \App\Models\Institute::whereIn('id', $this->institute_ids ?? [])->get();
}
}