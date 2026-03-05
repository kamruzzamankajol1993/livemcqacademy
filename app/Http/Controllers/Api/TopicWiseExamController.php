<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\McqQuestion;
use App\Models\TopicWiseExam;
use App\Models\Exam;
use Illuminate\Support\Facades\Validator;
use DB;

class TopicWiseExamController extends Controller
{
    /**
     * ১. কাস্টম এক্সাম শুরু করা (Subject/Chapter/Topic Wise)
     * এখানে ইউজার প্রশ্ন লিমিট এবং সময় ইনপুট দিবে।
     */
    public function startExam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_id'     => 'required|exists:subjects,id',
            'chapter_id'     => 'nullable|exists:chapters,id',
            'topic_id'       => 'nullable|exists:topics,id',
            'question_limit' => 'required|integer|min:1',
            'exam_duration'  => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        return $this->generateExamProcess($request, $request->question_limit, $request->exam_duration);
    }

    /**
     * ২. স্পেশাল এক্সাম শুরু করা (Board/Institute Wise)
     * এখানে লিমিট এবং সময় Exam টেবিল থেকে আসবে (Category 7 ও 8)।
     */
    public function startSpecialExam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_id'   => 'required|exists:subjects,id',
            'board_id'     => 'nullable|exists:boards,id',
            'institute_id' => 'nullable|exists:institutes,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        // ক্যাটাগরি আইডি নির্ধারণ: বোর্ড হলে ৭, ইনস্টিটিউট হলে ৮
        $categoryId = $request->filled('board_id') ? 7 : 8;
        
        $examConfig = Exam::whereJsonContains('exam_category_ids', (string)$categoryId)
                        ->where('status', 1)
                        ->first();

        if (!$examConfig) {
            return response()->json(['status' => false, 'message' => 'পরীক্ষার কনফিগারেশন পাওয়া যায়নি।'], 404);
        }

        return $this->generateExamProcess($request, $examConfig->total_questions, $examConfig->exam_duration_minutes, true);
    }

    /**
     * ৩. কমন প্রশ্ন জেনারেশন এবং এক্সাম তৈরি লজিক
     */
    private function generateExamProcess($request, $limit, $duration, $isSpecial = false)
    {
        $query = McqQuestion::where('subject_id', $request->subject_id)->where('status', 1);

        // ফিল্টারিং লজিক
        if ($isSpecial) {
            if ($request->filled('board_id')) {
                $query->whereJsonContains('board_ids', (string)$request->board_id);
            } elseif ($request->filled('institute_id')) {
                $query->whereJsonContains('institute_ids', (string)$request->institute_id);
            }
        } else {
            if ($request->filled('chapter_id')) $query->where('chapter_id', $request->chapter_id);
            if ($request->filled('topic_id')) $query->where('topic_id', $request->topic_id);
        }

        $availableCount = $query->count();
        if ($availableCount == 0) {
            return response()->json(['status' => false, 'message' => 'কোনো প্রশ্ন পাওয়া যায়নি।'], 404);
        }

        $finalLimit = min($limit, $availableCount);
        $questions = $query->inRandomOrder()->limit($finalLimit)->get();

        $exam = TopicWiseExam::create([
            'user_id'        => auth()->id(),
            'subject_id'     => $request->subject_id,
            'chapter_id'     => $request->chapter_id,
            'topic_id'       => $request->topic_id,
            'board_id'       => $request->board_id,
            'institute_id'   => $request->institute_id,
            'question_limit' => $finalLimit,
            'exam_duration'  => $duration,
            'questions_data' => $questions->toArray(),
            'status'         => 'pending'
        ]);

        $formattedQuestions = $questions->map(fn($q) => [
            'id'       => $q->id,
            'question' => $q->question,
            'option_1' => $q->option_1,
            'option_2' => $q->option_2,
            'option_3' => $q->option_3,
            'option_4' => $q->option_4,
        ]);

        return response()->json([
            'status'    => true,
            'exam_id'   => $exam->id,
            'limit'     => $finalLimit,
            'duration'  => $duration,
            'questions' => $formattedQuestions
        ]);
    }

    /**
     * ৪. উত্তর সাবমিট করা এবং ব্যাখ্যাসহ রেজাল্ট প্রদান
     */
    public function submitExam(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|exists:topic_wise_exams,id',
            'answers' => 'required|array', // Format: [{"question_id": 1, "answer": "1"}]
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        $exam = TopicWiseExam::where('id', $request->exam_id)
                            ->where('user_id', auth()->id())
                            ->firstOrFail();
        
        if ($exam->status == 'completed') {
            return response()->json(['status' => false, 'message' => 'এই পরীক্ষাটি ইতিমধ্যে সাবমিট করা হয়েছে।'], 400);
        }

        $correct = 0;
        $wrong = 0;
        $questions = collect($exam->questions_data);
        $resultDetails = [];

        foreach ($request->answers as $ans) {
            $question = $questions->firstWhere('id', $ans['question_id']);
            if ($question) {
                $isCorrect = (string)$question['answer'] === (string)$ans['answer'];
                if ($isCorrect) $correct++; else $wrong++;

                $resultDetails[] = [
                    'question_id'    => $question['id'],
                    'question_text'  => $question['question'],
                    'user_answer'    => $ans['answer'],
                    'correct_answer' => $question['answer'],
                    'is_correct'     => $isCorrect,
                    'explanation'    => $question['short_description'] ?? 'ব্যাখ্যা পাওয়া যায়নি।'
                ];
            }
        }

        $exam->update([
            'user_answers'    => $request->answers,
            'correct_answers' => $correct,
            'wrong_answers'   => $wrong,
            'earned_marks'    => $correct, // প্রতি প্রশ্নের মান ১ হিসেবে
            'status'          => 'completed'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'পরীক্ষা সফলভাবে সম্পন্ন হয়েছে।',
            'result' => [
                'total_questions' => $exam->question_limit,
                'correct'         => $correct,
                'wrong'           => $wrong,
                'earned_marks'    => $correct,
                'details'         => $resultDetails
            ]
        ]);
    }

    /**
     * ৫. ইউজারের পার্সোনাল এক্সাম হিস্ট্রি
     */
    public function examHistory()
    {
        $history = TopicWiseExam::with('subject:id,name_en,name_bn')
            ->where('user_id', auth()->id())
            ->latest()
            ->paginate(15);

        return response()->json([
            'status' => true,
            'data' => $history
        ]);
    }

    public function examHistoryDetail(Request $request)
    {
        $id = $request->exam_id;

        if (!$id) return response()->json(['status' => false, 'message' => 'Exam ID is required'], 400);

        $exam = TopicWiseExam::with('subject:id,name_en,name_bn')
            ->where('id', $id)
            ->where('user_id', auth()->id())
            ->first();

        if (!$exam) return response()->json(['status' => false, 'message' => 'History not found'], 404);

        $questions = collect($exam->questions_data);
        $userAnswers = collect($exam->user_answers);
        $reviewDetails = [];

        foreach ($questions as $question) {
            $ua = $userAnswers->firstWhere('question_id', $question['id']);
            
            $reviewDetails[] = [
                'id'             => $question['id'],
                'question'       => $question['question'],
                'options'        => [
                    '1' => $question['option_1'],
                    '2' => $question['option_2'],
                    '3' => $question['option_3'],
                    '4' => $question['option_4'],
                ],
                'user_answer'    => $ua['answer'] ?? null,
                'correct_answer' => $question['answer'],
                'is_correct'     => isset($ua['answer']) && ($ua['answer'] == $question['answer']),
                'explanation'    => $question['short_description'] ?? 'ব্যাখ্যা পাওয়া যায়নি।'
            ];
        }

        return response()->json([
            'status' => true,
            'exam_info' => [
                'subject' => $exam->subject->name_en ?? null,
                'correct' => $exam->correct_answers,
                'wrong'   => $exam->wrong_answers,
                'total'   => $exam->question_limit,
                'date'    => $exam->created_at->format('d M Y, h:i A')
            ],
            'review' => $reviewDetails
        ]);
    }

}

