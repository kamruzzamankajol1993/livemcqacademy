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
        'section_id',          // New: Added section support
        'institute_ids',       // New: Changed to multiple (JSON)
        'board_ids',           // New: Changed to multiple (JSON)
        'topic_id',
        'mcq_type',            // New: 'text' or 'image'
        'question',
        'question_img',        // New: Image path for question
        'option_1',
        'option_1_img',        // New: Image path for option 1
        'option_2',
        'option_2_img',        // New: Image path for option 2
        'option_3',
        'option_3_img',        // New: Image path for option 3
        'option_4',
        'option_4_img',        // New: Image path for option 4
        'answer',
        'tags',
        'short_description',
        'view_count',
        'total_likes',
        'total_dislikes',
        'upload_type',
        'status',
    ];

    /**
     * The attributes that should be cast.
     * * institute_ids এবং board_ids এখন মাল্টিপল আইডি হোল্ড করবে।
     */
    protected $casts = [
        'tags' => 'array',
        'institute_ids' => 'array', 
        'board_ids' => 'array',
    ];

    // --- Relationships ---

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

    public function department()
    {
        return $this->belongsTo(ClassDepartment::class, 'class_department_id');
    }

    /**
     * Section Relationship
     */
    public function section()
    {
        return $this->belongsTo(Section::class, 'section_id');
    }

    /**
     * যেহেতু institute_ids এখন একটি JSON অ্যারে, তাই সরাসরি belongsTo কাজ করবে না।
     * আপনি চাইলে এই মেথডটি ব্যবহার করে সব ইনস্টিটিউট গেট করতে পারেন।
     */
    public function getInstitutesAttribute()
    {
        return Institute::whereIn('id', $this->institute_ids ?? [])->get();
    }

    /**
     * বোর্ডগুলোর জন্য একই লজিক।
     */
    public function getBoardsAttribute()
    {
        return Board::whereIn('id', $this->board_ids ?? [])->get();
    }
}