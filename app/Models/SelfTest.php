<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SelfTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'board_id',
        'class_id',
        'subject_id',
        'question_limit',
        'time_duration',
        'negative_mark',
        'pass_mark',
        'per_question_mark',
        'correct_answers',
        'wrong_answers',
        'earned_marks',
        'result_status'
    ];

    // রিলেশনশিপসমূহ
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function board()
    {
        return $this->belongsTo(Board::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }
}