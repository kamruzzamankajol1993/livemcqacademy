<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TopicWiseExam extends Model
{
    protected $fillable = [
        'user_id', 'subject_id', 'chapter_id', 'topic_id','board_id','institute_id',
        'question_limit', 'exam_duration', 'questions_data', 
        'user_answers', 'earned_marks', 'correct_answers', 
        'wrong_answers', 'status'
    ];

    protected $casts = [
        'questions_data' => 'array',
        'user_answers' => 'array',
    ];

    public function subject() { return $this->belongsTo(Subject::class); }
}
