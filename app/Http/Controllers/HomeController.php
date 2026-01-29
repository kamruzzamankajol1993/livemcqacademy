<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\McqQuestion;
use App\Models\Subject;
use App\Models\SchoolClass;
use App\Models\Institute;
use App\Models\User;
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
        $now = Carbon::now();

        // --- 1. Summary Counts ---
        $totalMcq = McqQuestion::count();
        $totalSubjects = Subject::count();
        $totalClasses = SchoolClass::count();
        $totalInstitutes = Institute::count();

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
                $newQuestionsQuery->whereMonth('created_at', $now->month)->whereYear('created_at', $now->year);
                break;
        }
        $newQuestionsCount = $newQuestionsQuery->count();

        // --- 3. Chart: MCQ Upload History (Last 6 Months) ---
        $mcqHistory = McqQuestion::select(
            DB::raw("DATE_FORMAT(created_at, '%b') as month"),
            DB::raw("YEAR(created_at) as year"),
            DB::raw("MONTH(created_at) as month_num"),
            DB::raw("COUNT(*) as total")
        )->where('created_at', '>=', Carbon::now()->subMonths(5)->startOfMonth())
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

        // --- 5. Recent MCQs Table ---
        $recentMcqs = McqQuestion::with(['class', 'subject'])
                        ->latest()
                        ->take(6)
                        ->get();

        // --- 6. Top Classes (Most Questions) ---
        $topClasses = McqQuestion::join('school_classes', 'mcq_questions.class_id', '=', 'school_classes.id')
            ->select('school_classes.name_en', DB::raw('count(*) as total'))
            ->groupBy('school_classes.name_en')
            ->orderBy('total', 'DESC')
            ->take(6)
            ->get();

        return view('admin.dashboard.index', compact(
            'totalMcq',
            'totalSubjects',
            'totalClasses',
            'totalInstitutes',
            'newQuestionsCount',
            'mcqChartData',
            'subjectChartData',
            'recentMcqs',
            'topClasses',
            'filter'
        ));
    }
}