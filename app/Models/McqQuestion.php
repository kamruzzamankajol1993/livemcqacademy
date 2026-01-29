<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class McqQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'class_id',
        'subject_id',
        'chapter_id',
        'class_department_id',
        'institute_id', // New
        'board_id',     // New
        'year_id',      // New (Academic Year)
        'topic_id',
        'question',
        'option_1',
        'option_2',
        'option_3',
        'option_4',
        'answer',
        'tags',
        'short_description',
        'view_count',
        'total_likes',
        'total_dislikes',
        'upload_type',
        'status',
    ];

    // Tags কলামকে JSON/Array হিসেবে কাস্টিং করা হলো
    protected $casts = [
        'tags' => 'array',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function class()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    public function topic()
    {
        return $this->belongsTo(Topic::class, 'topic_id');
    }

    public function institute()
    {
        return $this->belongsTo(Institute::class, 'institute_id');
    }

    public function board()
    {
        return $this->belongsTo(Board::class, 'board_id');
    }

    public function academicYear()
    {
        return $this->belongsTo(AcademicYear::class, 'year_id');
    }

    public function department()
    {
        // Relationship with ClassDepartment model
        return $this->belongsTo(ClassDepartment::class, 'class_department_id');
    }
}