<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ExamResult;
use App\Models\ExamPayment;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    // ১. এক্সাম এনরোলমেন্ট রিপোর্ট
    public function examEnrollment(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::table('exam_results')
                ->join('exam_packages', 'exam_results.exam_package_id', '=', 'exam_packages.id')
                ->select('exam_packages.exam_name', DB::raw('count(exam_results.id) as total_students'))
                ->groupBy('exam_packages.exam_name');

            $this->applyFilters($query, $request, 'exam_results.created_at');

            $data = $query->paginate(15);
            return response()->json($this->formatResponse($data));
        }
        return view('admin.reports.exam_enrollment');
    }

    // ২. এক্সাম পেমেন্ট রিপোর্ট
    public function examPayments(Request $request)
    {
        if ($request->ajax()) {
            $query = ExamPayment::with(['user', 'examPackage']);

            $this->applyFilters($query, $request, 'created_at');

            $data = $query->latest()->paginate(15);
            return response()->json($this->formatResponse($data));
        }
        return view('admin.reports.exam_payments');
    }

    // ৩. বুক পেমেন্ট রিপোর্ট
    public function bookPayments(Request $request)
    {
        if ($request->ajax()) {
            $query = DB::table('book_payments')
                ->join('users', 'book_payments.user_id', '=', 'users.id')
                ->join('books', 'book_payments.book_id', '=', 'books.id')
                ->select('book_payments.*', 'users.name as user_name', 'books.title as book_title');

            $this->applyFilters($query, $request, 'book_payments.created_at');

            $data = $query->paginate(15);
            return response()->json($this->formatResponse($data));
        }
        return view('admin.reports.book_payments');
    }

    // ফিল্টার লজিক হেল্পার
    private function applyFilters($query, $request, $dateColumn)
    {
        if ($request->filled('year')) {
            $query->whereYear($dateColumn, $request->year);
        }
        if ($request->filled('month')) {
            $query->whereMonth($dateColumn, $request->month);
        }
        if ($request->filled('date_range')) {
            $dates = explode(' - ', $request->date_range);
            $query->whereBetween($dateColumn, [
                Carbon::parse($dates[0])->startOfDay(), 
                Carbon::parse($dates[1])->endOfDay()
            ]);
        }
    }

    private function formatResponse($data) {
        return [
            'data'         => $data->items(),
            'total'        => $data->total(),
            'current_page' => $data->currentPage(),
            'last_page'    => $data->lastPage(),
            'from'         => $data->firstItem(),
            'to'           => $data->lastItem(),
        ];
    }


    // ৪. কোন প্যাকেজে কত স্টুডেন্ট এনরোল করেছে
public function packageEnrollment(Request $request)
{
    if ($request->ajax()) {
        $query = DB::table('user_subscriptions')
            ->join('packages', 'user_subscriptions.package_id', '=', 'packages.id')
            ->select('packages.name as package_name', DB::raw('count(user_subscriptions.id) as total_enrollments'))
            ->groupBy('packages.name');

        $this->applyFilters($query, $request, 'user_subscriptions.created_at');

        $data = $query->paginate(15);
        return response()->json($this->formatResponse($data));
    }
    return view('admin.reports.package_enrollment');
}

// ৫. প্যাকেজ পেমেন্ট ইনফো
public function packagePayments(Request $request)
{
    if ($request->ajax()) {
        $query = Payment::with(['user', 'package']);

        $this->applyFilters($query, $request, 'created_at');

        $data = $query->latest()->paginate(15);
        return response()->json($this->formatResponse($data));
    }
    return view('admin.reports.package_payments');
}


// ৬. স্টুডেন্ট ইনফো রিপোর্ট
public function studentInfo(Request $request)
{
    if ($request->ajax()) {
        // রিলেশনসহ কাস্টমার কুয়েরি
        $query = Customer::with(['user.activeSubscription.package', 'user.schoolClass']);

        // ফিল্টার লজিক প্রয়োগ
        $this->applyFilters($query, $request, 'customers.created_at');

        $data = $query->latest('id')->paginate(15);
        return response()->json($this->formatResponse($data));
    }
    return view('admin.reports.student_info');
}
}