<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\McqQuestion;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Institute;
use App\Models\Book; // নতুন যুক্ত হয়েছে
use App\Models\Customer;
use App\Models\UserSubscription;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $filter = $request->get('filter', 'this_month');
        // এশিয়া/ঢাকা টাইমজোন সেট করা
        $now = Carbon::now('Asia/Dhaka');

        // --- 1. Summary Counts & Metrics ---
        $totalMcq = McqQuestion::count();
        $totalSubjects = Subject::count();
        $totalClasses = SchoolClass::count();
        $totalInstitutes = Institute::count();
        $totalBooks = Book::count(); // বুক মডিউলের ডাটা
        
        $totalCustomers = Customer::count();
        $activeSubscriptions = UserSubscription::where('status', 'active')
                                ->where('end_date', '>', $now)
                                ->count();
        $totalEarnings = Payment::where('status', 'success')->sum('amount');

        // --- 2. Filter Logic for "New Questions" Card ---
        $newQuestionsQuery = McqQuestion::query();
        switch ($filter) {
            case 'today':
                $newQuestionsQuery->whereDate('created_at', $now->today());
                break;
            case 'this_year':
                $newQuestionsQuery->whereYear('created_at', $now->year);
                break;
            case 'this_month':
            default:
                $newQuestionsQuery->whereMonth('created_at', $now->month)
                                  ->whereYear('created_at', $now->year);
                break;
        }
        $newQuestionsCount = $newQuestionsQuery->count();

        // --- 3. Chart: MCQ Upload History (Last 6 Months) ---
        $mcqHistory = McqQuestion::select(
            DB::raw("DATE_FORMAT(created_at, '%b') as month"),
            DB::raw("YEAR(created_at) as year"),
            DB::raw("MONTH(created_at) as month_num"),
            DB::raw("COUNT(*) as total")
        )->where('created_at', '>=', $now->copy()->subMonths(5)->startOfMonth())
        ->groupBy('year', 'month_num', 'month')
        ->orderBy('year', 'ASC')
        ->orderBy('month_num', 'ASC')
        ->get();

        $mcqChartData = [['Month', 'Questions Uploaded']];
        foreach ($mcqHistory as $row) {
            $mcqChartData[] = [$row->month, (int)$row->total];
        }

        // --- 4. Chart: Questions by Subject (Top 5) ---
        $subjectDistribution = McqQuestion::join('subjects', 'mcq_questions.subject_id', '=', 'subjects.id')
            ->select('subjects.name_en', DB::raw('count(*) as total'))
            ->groupBy('subjects.name_en')
            ->orderBy('total', 'DESC')
            ->take(5)
            ->get();

        $subjectChartData = [['Subject', 'Total Questions']];
        foreach ($subjectDistribution as $row) {
            $subjectChartData[] = [$row->name_en, (int)$row->total];
        }

        // --- 5. Upcoming Expiring Subscriptions (Next 7 Days) ---
        $expiringSoon = UserSubscription::with(['user.customer', 'package'])
            ->where('status', 'active')
            ->whereBetween('end_date', [$now, $now->copy()->addDays(7)])
            ->orderBy('end_date', 'asc')
            ->get();

        // --- 6. Recent Data Tables ---
        $recentMcqs = McqQuestion::with(['class', 'subject'])
                        ->latest()
                        ->take(6)
                        ->get();

        $recentBooks = Book::with(['category', 'subject'])
                        ->latest()
                        ->take(6)
                        ->get(); // নতুন বুক ডাটা

        // --- 7. Top Classes (Most Questions) ---
        $topClasses = McqQuestion::join('school_classes', 'mcq_questions.class_id', '=', 'school_classes.id')
            ->select('school_classes.name_en', DB::raw('count(*) as total'))
            ->groupBy('school_classes.name_en')
            ->orderBy('total', 'DESC')
            ->take(6)
            ->get();

        return view('admin.dashboard.index', compact(
            'totalCustomers',
            'activeSubscriptions',
            'totalMcq',
            'totalEarnings',
            'expiringSoon',
            'totalSubjects',
            'totalClasses',
            'totalInstitutes',
            'totalBooks',
            'newQuestionsCount',
            'mcqChartData',
            'subjectChartData',
            'recentMcqs',
            'recentBooks',
            'topClasses',
            'filter'
        ));
    }
}