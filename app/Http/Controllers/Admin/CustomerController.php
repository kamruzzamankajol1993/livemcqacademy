<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Package;
use App\Models\Payment;
use App\Models\UserSubscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;
use Illuminate\Validation\Rules;
use Hash;
use Illuminate\Support\Facades\Http;

class CustomerController extends Controller
{

    public function bulkUpdateType(Request $request)
{
    $request->validate([
        'ids' => 'required|array',
        'type' => 'required|in:normal,silver,platinum',
    ]);

    try {
        $discount = 0;
        if($request->type == 'silver') $discount = 5;
        if($request->type == 'platinum') $discount = 10;

        // Update Type and Discount for all selected IDs
        Customer::whereIn('id', $request->ids)->update([
            'type' => $request->type,
            'discount_in_percent' => $discount
        ]);

        return response()->json(['success' => true, 'message' => 'Selected customers updated successfully.']);
    } catch (Exception $e) {
        Log::error('Bulk update failed: ' . $e->getMessage());
        return response()->json(['success' => false, 'message' => 'Failed to update.'], 500);
    }
}
    public function index()
    {
        try {
            return view('admin.customer.index');
        } catch (Exception $e) {
            Log::error('Failed to load customer index page: ' . $e);
            return redirect()->back()->with('error', 'Could not load the page.');
        }
    }

    public function data(Request $request)
{
    try {
        // user এর সাথে activeSubscription এবং তার package রিলেশন লোড করা হয়েছে
        $query = Customer::with(['user.activeSubscription.package']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%')
                  ->orWhere('phone', 'like', '%' . $search . '%');
            });
        }

        $data = $query->orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'data' => $data->items(),
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'per_page' => $data->perPage(),
            'from' => $data->firstItem(),
            'to' => $data->lastItem(),
        ]);
    } catch (Exception $e) {
        Log::error("Error fetching customer data: " . $e->getMessage());
        return response()->json(['error' => 'Something went wrong!'], 500);
    }
}

    public function create()
    {

        
        try {
            return view('admin.customer.create');
        } catch (Exception $e) {
            Log::error('Failed to load create customer page: ' . $e);
            return redirect()->back()->with('error', 'Could not load the page.');
        }
    }

     public function store(Request $request)
{
    $request->validate([
        'name' => 'required',
        'email' => 'required|email|unique:users,email',
        'phone' => 'required|unique:users,phone',
        'password' => 'required|confirmed|min:6',
    ]);

    try {
        DB::beginTransaction();

        // ১. ইউজার টেবিলে ডাটা সেভ (Login Access)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'viewpassword' => $request->password, // আপনার সিস্টেমে এটা থাকলে রাখবেন
            'user_type' => 2,
            'status' => 1,
        ]);

        // ২. কাস্টমার টেবিলে ডাটা সেভ
        Customer::create([
            'user_id' => $user->id,
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => 1,
        ]);

        DB::commit();
        return redirect()->route('student.index')->with('success', 'Customer & Login access created successfully!');
    } catch (Exception $e) {
        DB::rollBack();
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}

    public function show($id)
    {
        try {
            // ইউজার ডাটা সহ কাস্টমার লোড
            $customer = Customer::with(['user.payments.package', 'user.subscriptions.package', 'user.activeSubscription'])->findOrFail($id);
            $packages = Package::where('status', 1)->get();
            
            return view('admin.customer.show', compact('customer', 'packages'));
        } catch (Exception $e) {
            Log::error("Error showing customer: " . $e->getMessage());
            return redirect()->back()->with('error', 'Customer not found.');
        }
    }

    // অ্যাডমিন থেকে প্যাকেজ অ্যাসাইন বা পেমেন্ট এন্ট্রি
  /**
 * Admin can assign or renew a package for a customer.
 * Logic: Renew if same package, Switch if different.
 * Handles 'unlimited' limits for exams and books.
 */
public function assignPackage(Request $request, $id)
{
    // ১. ভ্যালিডেশন
    $request->validate([
        'package_id' => 'required|exists:packages,id',
        'amount' => 'required|numeric',
        'payment_method' => 'required'
    ]);

    try {
        DB::beginTransaction();

        $customer = Customer::findOrFail($id);
        $user = User::findOrFail($customer->user_id);
        $package = Package::with('features')->findOrFail($request->package_id);
        
        // বর্তমান সময় এবং প্যাকেজ অনুযায়ী মেয়াদ নির্ধারণ (BST Time)
        $now = Carbon::now('Asia/Dhaka'); 
        $durationDays = ($package->type == 'yearly') ? 365 : 30;

        // ২. ফিচার লিস্ট থেকে প্যাকেজের লিমিটগুলো সংগ্রহ করা
        // কোডগুলো আপনার feature_lists টেবিলের 'code' কলামের সাথে মিল থাকতে হবে
        $examLimit = $package->features->where('code', 'paid_exam_package')->first()->pivot->value ?? 0;
        $bookLimit = $package->features->where('code', 'download_pdf_book_mcq')->first()->pivot->value ?? 0;

        // ৩. পেমেন্ট রেকর্ড তৈরি করা
        $payment = Payment::create([
            'user_id' => $user->id,
            'package_id' => $package->id,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'status' => 'success',
            'trx_id' => 'ADMIN-'.time(),
            'payment_details' => [
                'note' => 'Assigned by Admin',
                'timezone' => 'Asia/Dhaka'
            ]
        ]);

        // ৪. একটিভ সাবস্ক্রিপশন চেক করা
        $activeSub = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('end_date', '>', $now)
            ->first();

        if ($activeSub) {
            if ($activeSub->package_id == $package->id) {
                // --- LOGIC 1: RENEW (একই প্যাকেজ) ---
                
                // এক্সাম লিমিট ক্যালকুলেশন (unlimited হলে যোগ করার প্রয়োজন নেই)
                if (strtolower($activeSub->remaining_exam_limit) == 'unlimited' || strtolower($examLimit) == 'unlimited') {
                    $finalExamLimit = 'unlimited';
                } else {
                    $finalExamLimit = (int)$activeSub->remaining_exam_limit + (int)$examLimit;
                }

                // বুক লিমিট ক্যালকুলেশন (unlimited হলে যোগ করার প্রয়োজন নেই)
                if (strtolower($activeSub->remaining_book_limit) == 'unlimited' || strtolower($bookLimit) == 'unlimited') {
                    $finalBookLimit = 'unlimited';
                } else {
                    $finalBookLimit = (int)$activeSub->remaining_book_limit + (int)$bookLimit;
                }

                // মেয়াদ বৃদ্ধি এবং লিমিট আপডেট
                $newEndDate = Carbon::parse($activeSub->end_date, 'Asia/Dhaka')->addDays($durationDays);
                
                $activeSub->update([
                    'end_date' => $newEndDate,
                    'payment_id' => $payment->id,
                    'remaining_exam_limit' => $finalExamLimit,
                    'remaining_book_limit' => $finalBookLimit,
                ]);
                
            } else {
                // --- LOGIC 2: SWITCH/UPGRADE (ভিন্ন প্যাকেজ) ---
                // আগের প্যাকেজ এক্সপায়ার করে দিয়ে নতুন লিমিট দিয়ে ফ্রেশ সাবস্ক্রিপশন শুরু হবে
                $activeSub->update(['status' => 'expired']);
                $this->createNewSubscription($user->id, $package->id, $payment->id, $now, $durationDays, $examLimit, $bookLimit);
            }
        } else {
            // --- LOGIC 3: NEW SUBSCRIPTION (আগে কোনো প্ল্যান নেই) ---
            $this->createNewSubscription($user->id, $package->id, $payment->id, $now, $durationDays, $examLimit, $bookLimit);
        }

        DB::commit();
        return redirect()->back()->with('success', 'Package assigned successfully with proper limits!');

    } catch (Exception $e) {
        DB::rollBack();
        Log::error("Package Assignment Failed: " . $e->getMessage());
        return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
    }
}

/**
 * Helper function updated to store initial limits.
 */
private function createNewSubscription($userId, $packageId, $paymentId, $now, $days, $examLimit, $bookLimit)
{
    return UserSubscription::create([
        'user_id' => $userId,
        'package_id' => $packageId,
        'payment_id' => $paymentId,
        'start_date' => $now,
        'end_date' => $now->copy()->addDays($days),
        'remaining_exam_limit' => $examLimit, //
        'remaining_book_limit' => $bookLimit, //
        'status' => 'active'
    ]);
}

    public function edit($id)
{
    try {
        $customer = Customer::findOrFail($id);
        return view('admin.customer.edit', compact('customer'));
    } catch (Exception $e) {
        Log::error("Error loading customer edit page: " . $e->getMessage());
        return redirect()->route('student.index')->with('error', 'Customer not found.');
    }
}

public function update(Request $request, $id)
{
    $customer = Customer::findOrFail($id);
    
    // ভ্যালিডেশন: ইমেইল এবং ফোন ইউনিক হতে হবে (বর্তমান ইউজারের আইডি বাদে)
    $request->validate([
        'name'  => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $customer->user_id,
        'phone' => 'required|unique:users,phone,' . $customer->user_id,
        'password' => 'nullable|confirmed|min:6',
    ]);

    try {
        DB::beginTransaction();

        // ১. ইউজার টেবিল আপডেট (Login Table)
        $user = User::findOrFail($customer->user_id);
        $userData = [
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ];

        // যদি পাসওয়ার্ড দেওয়া হয়, তবেই আপডেট হবে
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
            $userData['viewpassword'] = $request->password; // ডেভলপার রেফারেন্সের জন্য
        }
        $user->update($userData);

        // ২. কাস্টমার টেবিল আপডেট
        $customer->update([
            'name'  => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
        ]);

        DB::commit();
        Log::info("Customer and User account updated: ID " . $id);
        return redirect()->route('student.index')->with('success', 'Customer updated successfully!');

    } catch (Exception $e) {
        DB::rollBack();
        Log::error("Error updating customer ID $id: " . $e->getMessage());
        return redirect()->back()->withInput()->with('error', 'Failed to update customer.');
    }
}

    public function destroy(Customer $customer)
    {
        try {
            if ($customer->user_id) {
                User::find($customer->user_id)->delete();
            }
            $customer->delete();
            Log::info('Customer deleted successfully.', ['id' => $customer->id]);
            return redirect()->route('student.index')->with('success', 'Customer deleted successfully.');
        } catch (Exception $e) {
            Log::error("Failed to delete customer ID {$customer->id}: " . $e);
            return redirect()->route('student.index')->with('error', 'Failed to delete customer.');
        }
    }

    public function studentReport(Request $request, $id)
{
    $student = User::findOrFail($id);

    if ($request->ajax()) {
        // ১. এক্সাম রেজাল্ট
        if ($request->type == 'exam') {
            $data = \App\Models\ExamResult::with('examPackage')
                ->where('user_id', $id)
                ->latest()
                ->paginate(10);
        } 
        // ২. সেলফ টেস্ট রেজাল্ট
        elseif ($request->type == 'self') {
            $data = \App\Models\SelfTest::where('user_id', $id)
                ->latest()
                ->paginate(10);
        }
        // ৩. কেনা বইয়ের তালিকা (বুক পেমেন্ট টেবিল থেকে)
        else {
            $data = DB::table('book_payments')
                ->join('books', 'book_payments.book_id', '=', 'books.id')
                ->where('book_payments.user_id', $id)
                ->where('book_payments.status', 'completed')
                ->select('book_payments.*', 'books.title as book_title', 'books.image as book_image')
                ->latest('book_payments.created_at')
                ->paginate(10);
        }

        return response()->json([
            'data'         => $data->items(),
            'total'        => $data->total(),
            'current_page' => $data->currentPage(),
            'last_page'    => $data->lastPage(),
            'from'         => $data->firstItem(),
            'to'           => $data->lastItem(),
        ]);
    }

    return view('admin.customer.report', compact('student'));
}
}