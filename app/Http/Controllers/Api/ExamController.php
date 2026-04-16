<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ExamPackage;
use App\Models\Exam;
use App\Models\McqQuestion;
use Illuminate\Http\Request;
use App\Models\ExamResult;
use App\Models\User;
class ExamController extends Controller
{

public function examHistoryDetail(Request $request)
{
    $user = auth()->user();
    $id=$request->id;

    // ১. রেজাল্ট ডাটা খুঁজে বের করা (নিরাপত্তার জন্য user_id চেক করা হয়েছে)
    $result = ExamResult::with('examPackage:id,exam_name,exam_type')
        ->where('id', $id)
        ->where('user_id', $user->id)
        ->first();

    if (!$result) {
        return response()->json(['status' => 'error', 'message' => 'Result not found'], 404);
    }

    // ২. সাবমিট করা উত্তরগুলো থেকে ভুল উত্তরগুলো ফিল্টার করা
    $wrongAnswersReview = [];
    $submittedAnswers = $result->submitted_answers; // এটি অলরেডি অ্যারে হিসেবে কাস্ট করা আছে

    if (!empty($submittedAnswers)) {
        foreach ($submittedAnswers as $ua) {
            $question = McqQuestion::find($ua['question_id']);

            // যদি উত্তর ভুল হয় (এবং খালি না থাকে) অথবা কোনোভাবে ডাটাবেসের উত্তরের সাথে না মিলে
            if ($question && !empty($ua['answer']) && $question->answer != $ua['answer']) {
                $wrongAnswersReview[] = [
                    'question_id'    => $question->id,
                    'question'       => $question->question,
                    'question_img'   => $question->question_img ? url($question->question_img) : null,
                    'your_answer'    => $ua['answer'],
                    'correct_answer' => $question->answer,
                    'description'    => $question->short_description, // ডেসক্রিপশন যোগ করা হলো
                    'options' => [
                        '1' => $question->option_1,
                        '2' => $question->option_2,
                        '3' => $question->option_3,
                        '4' => $question->option_4,
                    ]
                ];
            }
        }
    }

    // ৩. আগের রেসপন্স স্ট্রাকচার ঠিক রেখে নতুন ডাটা রিটার্ন
    return response()->json([
        'status' => 'success',
        'data'   => $result,
        'review' => [
            'wrong_answers_details' => $wrongAnswersReview
        ]
    ]);
}

/**
 * ১. ইউজারের পরীক্ষার হিস্ট্রি লিস্ট
 */
public function examHistory(Request $request)
{
    $user = auth()->user();

    $history = ExamResult::with('examPackage:id,exam_name,exam_type')
        ->where('user_id', $user->id)
        ->latest()
        ->paginate(10);

    return response()->json([
        'status' => 'success',
        'data' => $history
    ]);
}

/**
 * ২. লিডারবোর্ড (র‍্যাঙ্কিং)
 * নির্দিষ্ট প্যাকেজ অনুযায়ী সর্বোচ্চ নম্বরধারীদের তালিকা
 */
public function leaderboard(Request $request)
{
    $packageId = $request->exam_package_id;

    if (!$packageId) {
        return response()->json(['status' => 'error', 'message' => 'Package ID is required'], 400);
    }

    $rankings = ExamResult::with('user:id,name,image')
        ->where('exam_package_id', $packageId)
        ->where('result_status', 'published') // শুধুমাত্র পাবলিশড রেজাল্টগুলো আসবে
        ->selectRaw('user_id, MAX(earned_marks) as best_score, created_at')
        ->groupBy('user_id')
        ->orderBy('best_score', 'desc')
        ->limit(20) // টপ ২০ জন
        ->get();

    return response()->json([
        'status' => 'success',
        'exam_package_id' => $packageId,
        'leaderboard' => $rankings
    ]);
}

public function submitExam(Request $request)
{
    $user = auth()->user();
    $packageId = $request->exam_package_id;
    $userAnswers = $request->answers; // Format: [{"question_id": 1, "answer": "1"}, ...]

    // ১. প্যাকেজ এবং এক্সাম সেটআপ ডাটা সংগ্রহ
    $package = ExamPackage::findOrFail($packageId);
    $examSetup = Exam::whereJsonContains('exam_category_ids', (string)$package->exam_category_id)->first();

    // ২. ক্যালকুলেশন ভেরিয়েবল
    $correctCount = 0;
    $wrongCount = 0;
    $skippedCount = 0;
    $wrongAnswersDetails = []; // ভুল উত্তরের ডিটেইলস রাখার জন্য অ্যারে
    
    $markPerQues = $examSetup ? $examSetup->per_question_mark : 1;
    // negative_marks যদি অ্যারে হয় তবে প্রথম ইনডেক্স নেওয়া হচ্ছে
    $negativeMark = $examSetup ? ($examSetup->negative_marks[0] ?? 0) : 0;

    // ৩. আনসার চেক লজিক
    foreach ($userAnswers as $ua) {
        $question = McqQuestion::find($ua['question_id']);
        
        if (!$question) continue;

        if (empty($ua['answer'])) {
            $skippedCount++;
        } elseif ($question->answer == $ua['answer']) {
            $correctCount++;
        } else {
            $wrongCount++;
            // ভুল উত্তরের জন্য ডিটেইলস সংগ্রহ
            $wrongAnswersDetails[] = [
                'question_id' => $question->id,
                'question' => $question->question,
                'your_answer' => $ua['answer'],
                'correct_answer' => $question->answer,
                'description' => $question->short_description // ডাটাবেসের short_description কলাম
            ];
        }
    }

    // ৪. মার্ক ও একুরেসি ক্যালকুলেশন
    $totalQuestions = count($userAnswers);
    $earnedMarks = ($correctCount * $markPerQues) - ($wrongCount * $negativeMark);
    $totalPossibleMarks = $totalQuestions * $markPerQues;
    $percentage = ($totalPossibleMarks > 0) ? ($earnedMarks / $totalPossibleMarks) * 100 : 0;

    // ৫. সাজেশন লজিক
    if ($percentage >= 80) $suggestion = "অসাধারণ! আপনার প্রস্তুতি খুব ভালো।";
    elseif ($percentage >= 50) $suggestion = "ভালো হয়েছে, তবে আরও অনুশীলনের প্রয়োজন।";
    else $suggestion = "আপনার এই বিষয়ে আরও গুরুত্ব দেওয়া উচিত।";

    // ৬. রেজাল্ট স্ট্যাটাস নির্ধারণ
    $activeSub = $user->activeSubscription; 
    $resultStatus = 'pending';

    // ইউজারের কেনা প্যাকেজ বা একটিভ সাবস্ক্রিপশন থাকলে পাবলিশড
    if ($package->exam_type == 'free' || $activeSub || $package->exam_type == 'paid') {
        $resultStatus = 'published'; 
    } else {
        $resultStatus = 'pending';
    }

    // ৭. ইউজারের ব্যক্তিগত সাবস্ক্রিপশন লিমিট কমানোর লজিক
    if ($package->exam_type == 'paid' && $activeSub) {
        $currentLimit = $activeSub->remaining_exam_limit;
        if (strtolower($currentLimit) !== 'unlimited' && (int)$currentLimit > 0) {
            $activeSub->remaining_exam_limit = (int)$currentLimit - 1;
            $activeSub->save();
        }
    }

    // ৮. ডাটাবেসে রেজাল্ট সেভ করা
    $result = ExamResult::create([
        'user_id' => $user->id,
        'exam_package_id' => $packageId,
        'submitted_answers' => $userAnswers,
        'total_questions' => $totalQuestions,
        'correct_answers' => $correctCount,
        'wrong_answers' => $wrongCount,
        'skipped_questions' => $skippedCount,
        'total_marks' => $totalPossibleMarks,
        'earned_marks' => $earnedMarks,
        'result_status' => $resultStatus,
        'suggestion_text' => $suggestion
    ]);

    // ৯. রেসপন্স প্রদান
    if ($resultStatus == 'published') {
        return response()->json([
            'status' => 'success',
            'message' => 'পরীক্ষা সম্পন্ন হয়েছে।',
            'result' => [
                'correct' => $correctCount,
                'wrong' => $wrongCount,
                'skipped' => $skippedCount,
                'marks' => round($earnedMarks, 2),
                'total' => $totalPossibleMarks,
                'accuracy' => round($percentage, 2) . '%',
                'suggestion' => $suggestion,
                'remaining_limit' => $activeSub ? $activeSub->remaining_exam_limit : null,
                'wrong_answers_details' => $wrongAnswersDetails // নতুন যুক্ত করা হয়েছে
            ]
        ]);
    } else {
        return response()->json([
            'status' => 'success',
            'message' => 'আপনার উত্তরপত্র জমা হয়েছে। ফ্রি এক্সাম হওয়ায় ২৪ ঘণ্টা পর রেজাল্ট পাবেন।'
        ]);
    }
}
    /**
     * সকল ডাটা লোড করার কমন মেথড (Eager Loading সহ)
     */
   private function getExamPackagesQuery(Request $request)
{
    $query = ExamPackage::with([
        'category:id,name_en,name_bn',
        'schoolClass:id,name_en,name_bn',
        'department:id,name_en,name_bn',
    ])->where('status', 1);

    // ফিল্টার অ্যাড করা
    if ($request->has('class_id')) {
        $query->where('class_id', $request->class_id);
    }

    if ($request->has('class_department_id')) {
        $query->where('class_department_id', $request->class_department_id);
    }

    if ($request->has('subject_id')) {
        $query->whereJsonContains('subject_ids', (string)$request->subject_id);
    }

    if ($request->has('board_id')) {
        $query->whereJsonContains('board_ids', (string)$request->board_id);
    }

    if ($request->has('institute_id')) {
        $query->whereJsonContains('institute_ids', (string)$request->institute_id);
    }

    return $query;
}

    /**
     * প্যাকেজ ডাটার সাথে সংশ্লিষ্ট Exam Setup ডাটা যুক্ত করা এবং অপ্রয়োজনীয় ফিল্ড হাইড করা
     */
   private function formatResponse($packages)
{
    $packages->getCollection()->transform(function ($package) {
        
        // --- ১. Exam টেবিল থেকে ডাটা নিয়ে আসা (আপনার রিকোয়ারমেন্ট অনুযায়ী) ---
        $examSetups = Exam::whereJsonContains('exam_category_ids', (string)$package->exam_category_id)
            ->where('status', 1)
            ->get();
        
        // 'exam_category_ids' হাইড করে 'exam_setups' এ রাখা
        $package->exam_setups = $examSetups->makeHidden(['exam_category_ids']);

        // --- ২. আইডি অনুযায়ী নামগুলো যুক্ত করা (Subject, Chapter, Topic) ---
        
        // সাবজেক্ট এর নাম ও আইডি
        $package->subject_details = !empty($package->subject_ids) 
            ? \App\Models\Subject::whereIn('id', $package->subject_ids)->select('id', 'name_en', 'name_bn')->get() 
            : [];

        // চ্যাপ্টার এর নাম ও আইডি
        $package->chapter_details = !empty($package->chapter_ids) 
            ? \App\Models\Chapter::whereIn('id', $package->chapter_ids)->select('id', 'name_en', 'name_bn')->get() 
            : [];

        // টপিক এর নাম ও আইডি
        $package->topic_details = !empty($package->topic_ids) 
            ? \App\Models\Topic::whereIn('id', $package->topic_ids)->select('id', 'name_en', 'name_bn')->get() 
            : [];

        return $package;
    });

    return response()->json($packages);
}

    // ১. সকল এক্সাম প্যাকেজ লিস্ট (Pagination সহ)
    public function index(Request $request)
{
    // রিকোয়েস্ট পাস করে কুয়েরি আনা
    $packages = $this->getExamPackagesQuery($request)->latest()->paginate(10);

    // রেসপন্স ফরম্যাট করে রিটার্ন করা
    return $this->formatResponse($packages);
}

    // ২. Class Wise লিস্ট
    // ২. ইউজারের প্রোফাইল অনুযায়ী Class Wise এক্সাম লিস্ট
public function classWise(Request $request)
{
    try {
        $user = auth()->user();

        // ১. চেক করা ইউজারের প্রোফাইলে ক্লাস আইডি আছে কি না
        if (empty($user->class_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'আপনার প্রোফাইলে ক্লাস সেট করা নেই। দয়া করে আগে একাডেমিক তথ্য আপডেট করুন।',
                'update_required' => true
            ], 403);
        }

        // ২. ইউজারের class_id অনুযায়ী অটো ফিল্টার
        $packages = $this->getExamPackagesQuery()
            ->where('class_id', $user->class_id)
            ->latest()
            ->paginate(10);

        return $this->formatResponse($packages);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

    // ৩. Department Wise লিস্ট
    // ৩. ইউজারের প্রোফাইল অনুযায়ী Department Wise লিস্ট
public function departmentWise(Request $request)
{
    try {
        $user = auth()->user();

        // ১. চেক করা ইউজারের প্রোফাইলে ডিপার্টমেন্ট আইডি আছে কি না
        if (empty($user->department_id)) {
            return response()->json([
                'status' => 'error',
                'message' => 'আপনার প্রোফাইলে কোনো ডিপার্টমেন্ট সেট করা নেই।',
                'no_department' => true
            ], 404);
        }

        // ২. ইউজারের department_id অনুযায়ী অটো ফিল্টার
        $packages = $this->getExamPackagesQuery()
            ->where('class_department_id', $user->department_id)
            ->latest()
            ->paginate(10);

        return $this->formatResponse($packages);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}

    // ৪. Subject Wise লিস্ট
    public function subjectWise(Request $request)
    {
        $packages = $this->getExamPackagesQuery()
            ->whereJsonContains('subject_ids', (string)$request->subject_id)
            ->latest()->paginate(10);
        return $this->formatResponse($packages);
    }
public function boardWise(Request $request)
{
    // এখানে সিঙ্গেল board_id পাস হবে
    $packages = $this->getExamPackagesQuery()
        ->whereJsonContains('board_ids', (string)$request->board_id)
        ->latest()->paginate(10);
    return $this->formatResponse($packages);
}

/**
 * ৬. Institute Wise এক্সাম প্যাকেজ লিস্ট
 * প্যাটার্ন: subjectWise() এর মতো
 */
public function instituteWise(Request $request)
{
    // এখানে সিঙ্গেল institute_id পাস হবে
    $packages = $this->getExamPackagesQuery()
        ->whereJsonContains('institute_ids', (string)$request->institute_id)
        ->latest()->paginate(10);
    return $this->formatResponse($packages);
}
 /**
 * একটি নির্দিষ্ট এক্সাম প্যাকেজের বিস্তারিত তথ্য (আপডেটেড)
 */
public function show(Request $request)
{
    $user = auth()->user();
    $id = $request->id;

    if (!$id) {
        return response()->json(['status' => 'error', 'message' => 'ID parameter is required'], 400);
    }

    $package = ExamPackage::with([
        'category:id,name_en,name_bn',
        'schoolClass:id,name_en,name_bn',
        'department:id,name_en,name_bn'
    ])->find($id);

    if (!$package) {
        return response()->json(['message' => 'Exam Package not found'], 404);
    }

    $examSetups = Exam::whereJsonContains('exam_category_ids', (string)$package->exam_category_id)
        ->where('status', 1)
        ->get()
        ->makeHidden(['exam_category_ids']);

    $activeSub = $user->activeSubscription; 
    
    // সিঙ্গেল পেমেন্ট ভ্যালিডিটি চেক (expire_date কলামের সাথে চেক হবে)
    $singlePurchase = \DB::table('exam_payments')
        ->where('user_id', $user->id)
        ->where('exam_package_id', $package->id)
        ->where('status', 'completed')
        ->where('expire_date', '>=', now()->format('Y-m-d H:i:s')) // BST অনুযায়ী চেক
        ->first();

    $can_exam = false;
    $must_pay = false;
    $message = "";
    $remaining_limit = null;

    // --- সময় ভিত্তিক এক্সেস কন্ট্রোল (নতুন লজিক) ---
    $now = now()->timezone('Asia/Dhaka');
    $startTime = \Carbon\Carbon::parse($package->start_time, 'Asia/Dhaka');
    $endTime = \Carbon\Carbon::parse($package->end_time, 'Asia/Dhaka');

    if ($now->lt($startTime)) {
        $can_exam = false;
        $message = "পরীক্ষাটি এখনও শুরু হয়নি। শুরু হবে: " . $startTime->format('d M, h:i A');
    } elseif ($now->gt($endTime)) {
        $can_exam = false;
        $message = "দুঃখিত, এই পরীক্ষার সময় অতিবাহিত হয়ে গেছে।";
    } else {
        // নির্ধারিত সময়ের ভেতরে থাকলে পেমেন্ট ও সাবস্ক্রিপশন চেক হবে
        if ($package->exam_type == 'free') {
            $can_exam = true;
            $message = "এটি একটি ফ্রি এক্সাম।";
        } else {
            if ($activeSub) {
                $remaining_limit = $activeSub->remaining_exam_limit;
                if (strtolower($remaining_limit) == 'unlimited' || (int)$remaining_limit > 0) {
                    $can_exam = true;
                    $message = "আপনার প্যাকেজ অনুযায়ী এক্সেস আছে।";
                } else {
                    if ($singlePurchase) {
                        $can_exam = true;
                        $message = "প্যাকেজ লিমিট শেষ হলেও আপনার আলাদা ক্রয়ের মেয়াদ আছে।";
                    } else {
                        $can_exam = false;
                        $must_pay = true;
                        $message = "প্যাকেজ লিমিট শেষ। পুনরায় পরীক্ষা দিতে পেমেন্ট করুন।";
                    }
                }
            } else {
                if ($singlePurchase) {
                    $can_exam = true;
                    $message = "আপনার ক্রয়ের মেয়াদ আছে। পরীক্ষা দিতে পারেন।";
                } else {
                    $can_exam = false;
                    $must_pay = true;
                    $message = "এই পরীক্ষাটি দিতে আপনাকে পেমেন্ট করতে হবে।";
                }
            }
        }
    }

    return response()->json([
        'status' => 'success',
        'data' => [
            'package_details' => $package,
            'exam_setups' => $examSetups,
            'access_control' => [
                'can_start_exam' => $can_exam,
                'must_pay' => $must_pay,
                'message' => $message,
                'remaining_limit' => $remaining_limit,
                'start_time' => $package->start_time, // নতুন
                'end_time' => $package->end_time,     // নতুন
                'payment_options' => $must_pay ? ['bkash', 'nagad', 'manual'] : []
            ]
        ]
    ]);
}


public function getQuestions(Request $request)
{
    $packageId = $request->id;

    if (!$packageId) {
        return response()->json(['status' => 'error', 'message' => 'Package ID is required'], 400);
    }

    // ১. প্যাকেজ ডাটা নেওয়া
    $package = ExamPackage::find($packageId);

    if (!$package) {
        return response()->json(['status' => 'error', 'message' => 'Exam Package not found'], 404);
    }

    // ২. Exam Setup টেবিল থেকে প্রশ্নের লিমিট নেওয়া
    $examSetup = Exam::whereJsonContains('exam_category_ids', (string)$package->exam_category_id)
                     ->where('status', 1)
                     ->first();

    $limit = $examSetup ? $examSetup->total_questions : 10;

    // ৩. McqQuestion টেবিল থেকে কুয়েরি শুরু করা
    $query = McqQuestion::where('class_id', $package->class_id)
                        ->where('status', 1);
                        
                       // dd( $query);

    // ৪. ক্যাটাগরি অনুযায়ী সাবজেক্ট, চ্যাপ্টার এবং টপিক ফিল্টারিং
    // এখানে empty চেক যোগ করা হয়েছে যাতে আইডি না থাকলে কুয়েরি ভুল না হয়
    if (in_array($package->exam_category_id, [5, 6, 7, 8, 9]) && !empty($package->subject_ids)) {
        $query->whereIn('subject_id', (array)$package->subject_ids)->get();
       
      
    }
     
    if (in_array($package->exam_category_id, [6, 7]) && !empty($package->chapter_ids)) {
        $query->whereIn('chapter_id', (array)$package->chapter_ids);
        
        
    }
    
    if ($package->exam_category_id == 7 && !empty($package->topic_ids)) {
        $query->whereIn('topic_id', (array)$package->topic_ids);
        
        //  dd($package->subject_ids);
    }

    // --- বোর্ড এবং ইনস্টিটিউট ফিল্টারিং লজিক ---
    $isBoardExam = !empty($package->board_ids);

    if ($isBoardExam) {
        // ১. যদি প্যাকেজে বোর্ড আইডি থাকে
        $query->where(function($q) use ($package) {
            foreach ((array)$package->board_ids as $boardId) {
                // ডাটাবেসে ID স্ট্রিং বা ইন্টিজার হিসেবে থাকতে পারে, তাই (string) কাস্টিং নিরাপদ
                $q->orWhereJsonContains('board_ids', (string)$boardId);
            }
        });
        
        
        // বোর্ডের ক্ষেত্রে সাধারণত সব প্রশ্ন দেখানো হয়
        $questions = $query->inRandomOrder()->get(); 
    } 
    elseif (!empty($package->institute_ids)) {
        
       // dd(1);
        
        // ২. যদি প্যাকেজে ইনস্টিটিউট আইডি থাকে
        $query->where(function($q) use ($package) {
            foreach ((array)$package->institute_ids as $instituteId) {
                $q->orWhereJsonContains('institute_ids', (string)$instituteId);
            }
        });
        $questions = $query->inRandomOrder()->limit($limit)->get();
    } 
    else {
        // ৩. সাধারণ এক্সাম প্যাকেজ (যদি উপরে কোনো বোর্ড/ইনস্টিটিউট না থাকে)
        $questions = $query->inRandomOrder()->limit($limit)->get();
    }

    // ৫. ডাটা ফরম্যাট করা
    $formattedQuestions = $questions->map(function ($q) {
        return [
            'id'           => $q->id,
            'question'     => $q->question,
          //  'question_img' => $q->question_img ? asset($q->question_img) : null, // ইমেজ থাকলে পাথসহ
            'option_1'     => $q->option_1,
           // 'option_1_img' => $q->option_1_img ? asset($q->option_1_img) : null,
            'option_2'     => $q->option_2,
           // 'option_2_img' => $q->option_2_img ? asset($q->option_2_img) : null,
            'option_3'     => $q->option_3,
           // 'option_3_img' => $q->option_3_img ? asset($q->option_3_img) : null,
            'option_4'     => $q->option_4,
           // 'option_4_img' => $q->option_4_img ? asset($q->option_4_img) : null,
            'mcq_type'     => $q->mcq_type,
        ];
    });

    // ৬. রেসপন্স পাঠানো
    $examInfo = [
        'package_id'  => $package->id,
        'exam_name'   => $package->exam_name,
        'category_id' => $package->exam_category_id,
        'total_found' => $questions->count(),
    ];

    if (!$isBoardExam) {
        $examInfo['question_limit'] = $limit;
    }

    return response()->json([
        'status'    => 'success',
        'exam_info' => $examInfo,
        'questions' => $formattedQuestions
    ]);
}


public function payForExam(Request $request)
{
    $request->validate([
        'exam_package_id' => 'required|exists:exam_packages,id',
        'payment_method' => 'required|in:bkash,nagad,manual',
        'amount' => 'required',
    ]);

    $examPackage = ExamPackage::find($request->exam_package_id);
    
    // validity_days এর বদলে প্যাকেজের end_time কেই expire_date হিসেবে সেট করা হয়েছে
    $expireDate = $examPackage->end_time;

    $data = [
        'user_id' => auth()->id(),
        'exam_package_id' => $request->exam_package_id,
        'payment_method' => $request->payment_method,
        'amount' => $request->amount,
        'expire_date' => $expireDate,
        'status' => ($request->payment_method == 'manual') ? 'pending' : 'completed',
        'created_at' => now()->timezone('Asia/Dhaka'),
        'updated_at' => now()->timezone('Asia/Dhaka'),
    ];

    if ($request->payment_method == 'manual') {
        $data['sender_number'] = $request->phone_number;
        $data['transaction_id'] = $request->transaction_id;
    }

    \DB::table('exam_payments')->insert($data);

    return response()->json([
        'status' => 'success',
        'message' => ($request->payment_method == 'manual') 
            ? 'আপনার ম্যানুয়াল পেমেন্টটি পেন্ডিং আছে।' 
            : "পেমেন্ট সফল। আপনি " . \Carbon\Carbon::parse($expireDate)->format('d M, h:i A') . " পর্যন্ত এই পরীক্ষাটি দিতে পারবেন।"
    ]);
}


}