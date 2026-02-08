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

    // ২. ক্যালকুলেশন ভেরিয়েবল
    $correctCount = 0;
    $wrongCount = 0;
    $skippedCount = 0;
    
    $markPerQues = $examSetup ? $examSetup->per_question_mark : 1;
    $negativeMark = $examSetup ? ($examSetup->negative_marks[0] ?? 0) : 0;

    // ৩. আনসার চেক লজিক
    foreach ($userAnswers as $ua) {
        $question = McqQuestion::find($ua['question_id']);
        
        if (empty($ua['answer'])) {
            $skippedCount++;
        } elseif ($question && $question->answer == $ua['answer']) {
            $correctCount++;
        } else {
            $wrongCount++;
        }
    }

    // ৪. মার্ক ও একুরেসি ক্যালকুলেশন
    $totalQuestions = count($userAnswers);
    $earnedMarks = ($correctCount * $markPerQues) - ($wrongCount * $negativeMark);
    $totalPossibleMarks = $totalQuestions * $markPerQues;
    $percentage = ($totalPossibleMarks > 0) ? ($earnedMarks / $totalPossibleMarks) * 100 : 0;

    // ৫. সাজেশন লজিক
    if ($percentage >= 80) $suggestion = "অসাধারণ! আপনার প্রস্তুতি খুব ভালো।";
    elseif ($percentage >= 50) $suggestion = "ভালো হয়েছে, তবে আরও অনুশীলনের প্রয়োজন।";
    else $suggestion = "আপনার এই বিষয়ে আরও গুরুত্ব দেওয়া উচিত।";

    // ৬. রেজাল্ট স্ট্যাটাস নির্ধারণ
    // ইউজার যাদের প্যাকেজ কেনা আছে তাদের ফ্রি/পেইড সব রেজাল্ট সাথে সাথে দিবে
    $activeSub = $user->activeSubscription; 
    $resultStatus = 'pending';

    if ($package->exam_type == 'paid' || $activeSub) {
        $resultStatus = 'published'; 
    } else {
        $resultStatus = 'pending'; // প্যাকেজ ছাড়া ফ্রি এক্সাম হলে ২৪ ঘণ্টা পর
    }

    // ৭. ইউজারের ব্যক্তিগত সাবস্ক্রিপশন লিমিট কমানোর লজিক
    if ($package->exam_type == 'paid' && $activeSub) {
        $currentLimit = $activeSub->remaining_exam_limit;

        // লিমিট যদি 'Unlimited' না হয় এবং ০ এর বেশি থাকে, তবেই কমবে
        if (strtolower($currentLimit) !== 'unlimited' && (int)$currentLimit > 0) {
            $activeSub->remaining_exam_limit = (int)$currentLimit - 1;
            $activeSub->save(); // শুধুমাত্র এই ইউজারের সাবস্ক্রিপশন রো আপডেট হবে
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
            'message' => 'পরীক্ষা সম্পন্ন হয়েছে।',
            'result' => [
                'correct' => $correctCount,
                'wrong' => $wrongCount,
                'skipped' => $skippedCount,
                'marks' => round($earnedMarks, 2),
                'total' => $totalPossibleMarks,
                'accuracy' => round($percentage, 2) . '%',
                'suggestion' => $suggestion,
                'remaining_limit' => $activeSub ? $activeSub->remaining_exam_limit : null
            ]
        ]);
    } else {
        return response()->json([
            'status' => 'success',
            'message' => 'আপনার উত্তরপত্র জমা হয়েছে। ফ্রি এক্সাম হওয়ায় ২৪ ঘণ্টা পর রেজাল্ট পাবেন।'
        ]);
    }
}
    /**
     * সকল ডাটা লোড করার কমন মেথড (Eager Loading সহ)
     */
    private function getExamPackagesQuery()
    {
        return ExamPackage::with([
            'category:id,name_en,name_bn',
            'schoolClass:id,name_en,name_bn',
            'department:id,name_en,name_bn'
        ])->where('status', 1);
    }

    /**
     * প্যাকেজ ডাটার সাথে সংশ্লিষ্ট Exam Setup ডাটা যুক্ত করা এবং অপ্রয়োজনীয় ফিল্ড হাইড করা
     */
    private function formatResponse($packages)
    {
        $packages->getCollection()->transform(function ($package) {
            // ক্যাটাগরি আইডি অনুযায়ী Exam টেবিল থেকে ডাটা নিয়ে আসা
            $examSetups = Exam::whereJsonContains('exam_category_ids', (string)$package->exam_category_id)
                ->where('status', 1)
                ->get();

            // প্রতিটি Exam অবজেক্ট থেকে 'exam_category_ids' ফিল্ডটি হাইড করা
            $package->exam_setups = $examSetups->makeHidden(['exam_category_ids']);
            
            return $package;
        });

        return response()->json($packages);
    }

    // ১. সকল এক্সাম প্যাকেজ লিস্ট (Pagination সহ)
    public function index()
    {
        $packages = $this->getExamPackagesQuery()->latest()->paginate(10);
        return $this->formatResponse($packages);
    }

    // ২. Class Wise লিস্ট
    public function classWise(Request $request)
    {
        $packages = $this->getExamPackagesQuery()
            ->where('class_id', $request->class_id)
            ->latest()->paginate(10);
        return $this->formatResponse($packages);
    }

    // ৩. Department Wise লিস্ট
    public function departmentWise(Request $request)
    {
        $packages = $this->getExamPackagesQuery()
            ->where('class_department_id', $request->department_id)
            ->latest()->paginate(10);
        return $this->formatResponse($packages);
    }

    // ৪. Subject Wise লিস্ট
    public function subjectWise(Request $request)
    {
        $packages = $this->getExamPackagesQuery()
            ->whereJsonContains('subject_ids', (string)$request->subject_id)
            ->latest()->paginate(10);
        return $this->formatResponse($packages);
    }

    /**
 * একটি নির্দিষ্ট এক্সাম প্যাকেজের বিস্তারিত তথ্য
 */
public function show(Request $request)
{
    $user = auth()->user();
    $id = $request->id;

    if (!$id) {
        return response()->json(['status' => 'error', 'message' => 'ID parameter is required'], 400);
    }

    // আপনার দেওয়া কোড: রিলেশনসহ প্যাকেজ ডাটা লোড করা
    $package = ExamPackage::with([
        'category:id,name_en,name_bn',
        'schoolClass:id,name_en,name_bn',
        'department:id,name_en,name_bn'
    ])->find($id);

    if (!$package) {
        return response()->json(['message' => 'Exam Package not found'], 404);
    }

    // আপনার দেওয়া কোড: প্যাকেজ ক্যাটাগরি অনুযায়ী Exam Setup ডাটা আনা
    $examSetups = Exam::whereJsonContains('exam_category_ids', (string)$package->exam_category_id)
        ->where('status', 1)
        ->get()
        ->makeHidden(['exam_category_ids']);

    // --- নতুন লজিক: লিমিট এবং এক্সেস কন্ট্রোল চেক ---

    // ইউজারের অ্যাক্টিভ সাবস্ক্রিপশন চেক করা
    $activeSub = $user->activeSubscription; 
    
    // সিঙ্গেল পেমেন্ট ভ্যালিডিটি চেক (যদি আলাদাভাবে কিনে থাকে)
    $singlePurchase = \DB::table('exam_payments')
        ->where('user_id', $user->id)
        ->where('exam_package_id', $package->id)
        ->where('status', 'completed')
        ->where('expire_date', '>=', now()->format('Y-m-d'))
        ->first();

    $can_exam = false;
    $must_pay = false;
    $message = "";
    $remaining_limit = null;

    if ($package->exam_type == 'free') {
        $can_exam = true;
        $message = "এটি একটি ফ্রি এক্সাম।";
    } else {
        // পেইড এক্সাম লজিক
        if ($activeSub) {
            // ইউজারের নিজস্ব সাবস্ক্রিপশন থেকে লিমিট চেক করা
            $remaining_limit = $activeSub->remaining_exam_limit;

            if (strtolower($remaining_limit) == 'unlimited' || (int)$remaining_limit > 0) {
                $can_exam = true;
                $message = "আপনার প্যাকেজ অনুযায়ী এক্সেস আছে। অবশিষ্ট লিমিট: " . $remaining_limit;
            } else {
                // লিমিট শেষ হলে সিঙ্গেল পারচেজ চেক
                if ($singlePurchase) {
                    $can_exam = true;
                    $message = "প্যাকেজ লিমিট শেষ হলেও আপনার আলাদা ক্রয়ের মেয়াদ আছে।";
                } else {
                    $can_exam = false;
                    $must_pay = true;
                    $message = "আপনার প্যাকেজ লিমিট শেষ। পুনরায় পরীক্ষা দিতে পেমেন্ট করুন।";
                }
            }
        } else {
            // সাবস্ক্রিপশন নেই, সিঙ্গেল পারচেজ চেক
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
                'validity_days' => $package->validity_days,
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

    // ৩. McqQuestion টেবিল থেকে কোয়েরি শুরু করা
    // এখানে class_id সব ক্যাটাগরির জন্যই কমন
    $query = McqQuestion::where('class_id', $package->class_id)
                        ->where('status', 1);

    /**
     * ক্যাটাগরি অনুযায়ী ফিল্টারিং লজিক:
     * প্যাকেজের subject_ids, chapter_ids, topic_ids যেহেতু JSON/Array, 
     * কিন্তু McqQuestion টেবিলে id গুলো Normal, তাই whereIn ব্যবহার করা হয়েছে।
     */

    // ক্যাটাগরি ৫, ৬, ৭ সবার জন্যই সাবজেক্ট ফিল্টার হবে
    if ($package->exam_category_id == 5 || $package->exam_category_id == 6 || $package->exam_category_id == 7) {
        $query->whereIn('subject_id', (array)$package->subject_ids);
    }

    // ক্যাটাগরি ৬ এবং ৭ এর জন্য চ্যাপ্টার ফিল্টার হবে
    if ($package->exam_category_id == 6 || $package->exam_category_id == 7) {
        $query->whereIn('chapter_id', (array)$package->chapter_ids);
    }

    // শুধুমাত্র ক্যাটাগরি ৭ এর জন্য টপিক ফিল্টার হবে
    if ($package->exam_category_id == 7) {
        $query->whereIn('topic_id', (array)$package->topic_ids);
    }

    // ৪. ফাইনাল ডাটা গেট করা
    $questions = $query->inRandomOrder()
                       ->limit($limit)
                       ->get();

    return response()->json([
        'status' => 'success',
        'exam_info' => [
            'package_id' => $package->id,
            'exam_name' => $package->exam_name,
            'category_id' => $package->exam_category_id,
            'question_limit' => $limit,
            'total_found' => $questions->count()
        ],
        'questions' => $questions
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
    
    // validity_days অনুযায়ী expire_date ক্যালকুলেট করা
    $expireDate = now()->addDays($examPackage->validity_days)->format('Y-m-d');

    $data = [
        'user_id' => auth()->id(),
        'exam_package_id' => $request->exam_package_id,
        'payment_method' => $request->payment_method,
        'amount' => $request->amount,
        'expire_date' => $expireDate,
        'status' => ($request->payment_method == 'manual') ? 'pending' : 'completed',
        'created_at' => now(),
        'updated_at' => now(),
    ];

    if ($request->payment_method == 'manual') {
        $data['sender_number'] = $request->phone_number;
        $data['transaction_id'] = $request->transaction_id;
    }

    \DB::table('exam_payments')->insert($data);

    return response()->json([
        'status' => 'success',
        'message' => ($request->payment_method == 'manual') 
            ? 'আপনার ম্যানুয়াল পেমেন্টটি পেন্ডিং আছে। অ্যাডমিন অ্যাপ্রুভ করলে পরীক্ষা দিতে পারবেন।' 
            : "পেমেন্ট সফল। আপনি আগামী $examPackage->validity_days দিন পর্যন্ত এই পরীক্ষাটি দিতে পারবেন।"
    ]);
}


}