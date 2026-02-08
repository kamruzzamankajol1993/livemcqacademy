<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SelfTest;
use App\Models\Board;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\McqQuestion;
use App\Models\Exam;
use Illuminate\Http\Request;
use DB;

class SelfTestController extends Controller
{
    /**
     * ১. কনফিগারেশন ডাটা গেট করা
     * ইউজারকে বোর্ড, ক্লাস এবং সাবজেক্ট লিস্ট দেখানোর জন্য
     */
    public function getConfig()
    {
        $data = [
            'boards' => Board::where('status', 1)->get(['id', 'name_en', 'name_bn']),
            'classes' => SchoolClass::where('status', 1)->get(['id', 'name_en', 'name_bn']),
            'subjects' => Subject::where('status', 1)->get(['id', 'name_en', 'name_bn']),
            // রেফারেন্সের জন্য এক্সাম টেবিল থেকে সেলফ টেস্ট ক্যাটাগরির ডিফল্ট লিমিট (ঐচ্ছিক)
            'default_setup' => Exam::where('status', 1)->first(['total_questions', 'time_duration', 'per_question_mark'])
        ];

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * ২. সেলফ টেস্ট শুরু করা
     * ইউজারের কাস্টম কনফিগারেশন অনুযায়ী প্রশ্ন জেনারেট করা
     */
    public function startSelfTest(Request $request)
    {
        $request->validate([
            'board_id'       => 'required|exists:boards,id',
            'class_id'       => 'required|exists:school_classes,id',
            'subject_id'     => 'required|exists:subjects,id',
            'question_limit' => 'required|integer|min:1',
            'time_duration'  => 'required|integer|min:1',
            'negative_mark'  => 'required|numeric',
            'pass_mark'      => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            // কাস্টম কনফিগারেশন সেভ
            $selfTest = SelfTest::create([
                'user_id'           => auth()->id(),
                'board_id'          => $request->board_id,
                'class_id'          => $request->class_id,
                'subject_id'        => $request->subject_id,
                'question_limit'    => $request->question_limit,
                'time_duration'     => $request->time_duration,
                'negative_mark'     => $request->negative_mark,
                'pass_mark'         => $request->pass_mark,
                'per_question_mark' => 1.00, // ডিফল্ট ১ রাখা হয়েছে
                'result_status'     => 'pending'
            ]);

            // McqQuestion টেবিল থেকে কাস্টম ফিল্টার অনুযায়ী র‍্যান্ডম প্রশ্ন আনা
            $questions = McqQuestion::where('board_id', $request->board_id)
                ->where('class_id', $request->class_id)
                ->where('subject_id', $request->subject_id)
                ->where('status', 1)
                ->inRandomOrder()
                ->limit($request->question_limit)
                ->get();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Self test generated successfully.',
                'self_test_id' => $selfTest->id,
                'config' => $selfTest,
                'questions' => $questions
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * ৩. রেজাল্ট সাবমিট করা
     */
    public function submitResult(Request $request)
    {
        $request->validate([
            'self_test_id' => 'required|exists:self_tests,id',
            'answers'      => 'required|array' // [{"question_id": 1, "answer": "2"}]
        ]);

        $selfTest = SelfTest::findOrFail($request->self_test_id);
        
        if ($selfTest->result_status !== 'pending') {
            return response()->json(['status' => 'error', 'message' => 'Result already submitted.'], 400);
        }

        $correctCount = 0;
        $wrongCount = 0;

        foreach ($request->answers as $ua) {
            $question = McqQuestion::find($ua['question_id']);
            if ($question) {
                if ($question->answer == $ua['answer']) {
                    $correctCount++;
                } else {
                    $wrongCount++;
                }
            }
        }

        // মার্ক ক্যালকুলেশন (কাস্টম নেগেটিভ মার্ক অনুযায়ী)
        $earnedMarks = ($correctCount * $selfTest->per_question_mark) - ($wrongCount * $selfTest->negative_mark);
        
        // পাস/ফেল নির্ধারণ
        $status = $earnedMarks >= $selfTest->pass_mark ? 'passed' : 'failed';

        // ডাটা আপডেট
        $selfTest->update([
            'correct_answers' => $correctCount,
            'wrong_answers'   => $wrongCount,
            'earned_marks'    => $earnedMarks,
            'result_status'   => $status,
            'user_responses'  => json_encode($request->answers)
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $status == 'passed' ? 'Congratulations! You passed.' : 'Sorry, you failed to reach the target.',
            'data' => [
                'correct' => $correctCount,
                'wrong' => $wrongCount,
                'earned_marks' => round($earnedMarks, 2),
                'pass_mark' => $selfTest->pass_mark,
                'result_status' => $status
            ]
        ]);
    }

    /**
     * ৪. ইউজারের সেলফ টেস্ট হিস্ট্রি
     */
    public function userHistory()
    {
        $history = SelfTest::with(['board', 'schoolClass', 'subject'])
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(10);

        return response()->json([
            'status' => 'success',
            'data' => $history
        ]);
    }

    /**
 * সাবজেক্ট ভিত্তিক সেলফ টেস্ট লিডারবোর্ড
 */
public function selfTestLeaderboard(Request $request)
{
    $subjectId = $request->subject_id;

    if (!$subjectId) {
        return response()->json(['status' => 'error', 'message' => 'Subject ID is required'], 400);
    }

    $rankings = SelfTest::with('user:id,name,image')
        ->where('subject_id', $subjectId)
        ->where('result_status', 'passed') // শুধুমাত্র যারা পাস করেছে
        ->selectRaw('user_id, MAX(earned_marks) as top_score, MIN(time_duration) as min_time')
        ->groupBy('user_id')
        ->orderBy('top_score', 'desc')
        ->orderBy('min_time', 'asc') // একই নম্বর হলে যে কম সময়ে পরীক্ষা দিয়েছে সে এগিয়ে থাকবে
        ->limit(20)
        ->get();

    return response()->json([
        'status' => 'success',
        'subject_id' => $subjectId,
        'leaderboard' => $rankings
    ]);
}

/**
     * ৪. রিভিউ আনসার (Review Answers)
     */
    public function reviewSelfTest(Request $request)
    {
        $id = $request->self_test_id;
        $selfTest = SelfTest::findOrFail($id);

        if (!$selfTest->user_responses) {
            return response()->json(['status' => 'error', 'message' => 'No responses found to review.'], 404);
        }

        $userResponses = json_decode($selfTest->user_responses, true);
        $questionIds = collect($userResponses)->pluck('question_id');
        
        $questions = McqQuestion::whereIn('id', $questionIds)->get();

        $reviewData = $questions->map(function($q) use ($userResponses) {
            $userAns = collect($userResponses)->where('question_id', $q->id)->first();
            
            return [
                'question' => $q->question_text,
                'options' => [
                    '1' => $q->option_1,
                    '2' => $q->option_2,
                    '3' => $q->option_3,
                    '4' => $q->option_4,
                ],
                'correct_answer' => $q->answer,
                'user_answer'    => $userAns ? $userAns['answer'] : null,
                'is_correct'     => $userAns && ($userAns['answer'] == $q->answer)
            ];
        });

        return response()->json([
            'status' => 'success',
            'total_correct' => $selfTest->correct_answers,
            'earned_marks' => $selfTest->earned_marks,
            'review' => $reviewData
        ]);
    }
}