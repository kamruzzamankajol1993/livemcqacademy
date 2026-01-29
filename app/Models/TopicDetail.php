<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TopicDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id',
        'class_id',
        'subject_id',
        'chapter_id',
        'topic_id',
        'topic_description',
        'status',
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