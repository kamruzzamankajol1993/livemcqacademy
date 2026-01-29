<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CqQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'class_id',
        'subject_id',
        'chapter_id',
        'topic_id',
        'stem',             // উদ্দীপক / মেইন টেক্সট
        'sub_questions',    // প্রশ্ন ও উত্তরের অ্যারে
        'tags',
        'short_description',
        'view_count',
        'total_likes',
        'total_dislikes',
        'upload_type',
        'status',
    ];

    // JSON কলামগুলোকে Array হিসেবে ব্যবহার করার জন্য Casting
    protected $casts = [
        'sub_questions' => 'array',
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
}