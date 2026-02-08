<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentManagementController extends Controller
{
    /**
     * বুক পেমেন্ট লিস্ট এবং AJAX ডাটা ফেচিং
     */
    public function bookPayments(Request $request)
    {
        // ১. কুয়েরি বিল্ডার সেটআপ (Eager Join সহ)
        $query = DB::table('book_payments')
            ->join('users', 'book_payments.user_id', '=', 'users.id')
            ->join('books', 'book_payments.book_id', '=', 'books.id')
            ->select(
                'book_payments.*', 
                'users.name as user_name', 
                'users.phone as user_phone', 
                'books.title as book_title'
            );

        // ২. সার্চ ফিল্টার (Transaction ID, User Name, Phone)
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('book_payments.transaction_id', 'like', '%' . $request->search . '%')
                  ->orWhere('users.name', 'like', '%' . $request->search . '%')
                  ->orWhere('book_payments.sender_number', 'like', '%' . $request->search . '%')
                  ->orWhere('users.phone', 'like', '%' . $request->search . '%');
            });
        }

        // ৩. স্ট্যাটাস ফিল্টার
        if ($request->filled('status')) {
            $query->where('book_payments.status', $request->status);
        }

        // ৪. প্যাগিনেশন
        $data = $query->latest('book_payments.created_at')->paginate(15);

        // ৫. AJAX রিকোয়েস্ট চেক
        if ($request->ajax()) {
            return response()->json($data);
        }

        return view('admin.payments.book_payments');
    }

    /**
     * এক্সাম পেমেন্ট লিস্ট এবং AJAX ডাটা ফেচিং
     */
    public function examPayments(Request $request)
    {
        $query = DB::table('exam_payments')
            ->join('users', 'exam_payments.user_id', '=', 'users.id')
            ->join('exam_packages', 'exam_payments.exam_package_id', '=', 'exam_packages.id')
            ->select(
                'exam_payments.*', 
                'users.name as user_name', 
                'users.phone as user_phone', 
                'exam_packages.exam_name'
            );

        // সার্চ ফিল্টার
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('exam_payments.transaction_id', 'like', '%' . $request->search . '%')
                  ->orWhere('users.name', 'like', '%' . $request->search . '%')
                  ->orWhere('users.phone', 'like', '%' . $request->search . '%');
            });
        }

        // স্ট্যাটাস ফিল্টার
        if ($request->filled('status')) {
            $query->where('exam_payments.status', $request->status);
        }

        $data = $query->latest('exam_payments.created_at')->paginate(15);

        if ($request->ajax()) {
            return response()->json($data);
        }

        return view('admin.payments.exam_payments');
    }

    /**
     * বুক পেমেন্ট স্ট্যাটাস আপডেট
     */
    public function updateBookPaymentStatus(Request $request, $id)
    {
        $status = $request->status;
        
        DB::table('book_payments')->where('id', $id)->update([
            'status' => $status,
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'বুক পেমেন্ট স্ট্যাটাস সফলভাবে আপডেট হয়েছে।');
    }

    /**
     * এক্সাম পেমেন্ট স্ট্যাটাস আপডেট
     */
    public function updateExamPaymentStatus(Request $request, $id)
    {
        $status = $request->status;

        DB::table('exam_payments')->where('id', $id)->update([
            'status' => $status,
            'updated_at' => now()
        ]);

        return redirect()->back()->with('success', 'এক্সাম পেমেন্ট স্ট্যাটাস সফলভাবে আপডেট হয়েছে।');
    }
}