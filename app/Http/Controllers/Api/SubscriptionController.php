<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Payment;
use App\Models\UserSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class SubscriptionController extends Controller
{
    /**
     * অ্যাপ থেকে প্যাকেজ কেনার এপিআই
     */
    public function purchasePackage(Request $request)
{
    // ১. ভ্যালিডেশন
    $request->validate([
        'package_id'     => 'required|exists:packages,id',
        'amount'         => 'required|numeric',
        'payment_method' => 'required',
        'trx_id'         => 'required|unique:payments,trx_id',
    ]);

    try {
        DB::beginTransaction();

        $user = $request->user();
        $package = Package::with('features')->findOrFail($request->package_id); // রিলেশনসহ লোড
        $now = Carbon::now('Asia/Dhaka');
        $durationDays = ($package->type == 'yearly') ? 365 : 30;

        // --- নতুন লিমিট সংগ্রহ (Exam & Book) ---
        $examFeature = $package->features->where('code', 'paid_exam_package')->first();
        $examLimitDefault = $examFeature ? $examFeature->pivot->value : '0';

        $bookFeature = $package->features->where('code', 'download_pdf_book_mcq')->first();
        $bookLimitDefault = $bookFeature ? $bookFeature->pivot->value : '0';

        // ২. পেমেন্ট রেকর্ড তৈরি
        $payment = Payment::create([
            'user_id'         => $user->id,
            'package_id'      => $package->id,
            'trx_id'          => $request->trx_id,
            'amount'          => $request->amount,
            'payment_method'  => $request->payment_method,
            'transaction_id'  => $request->trx_id,
            'status'          => 'success',
            'payment_details' => ['source' => 'Mobile App', 'timezone' => 'BST']
        ]);

        // ৩. বর্তমান একটিভ সাবস্ক্রিপশন চেক
        $activeSub = UserSubscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('end_date', '>', $now)
            ->first();

        if ($activeSub) {
            if ($activeSub->package_id == $package->id) {
                // --- রিনিউ লজিক (একই প্যাকেজ) ---
                $newEndDate = Carbon::parse($activeSub->end_date, 'Asia/Dhaka')->addDays($durationDays);

                // এক্সাম লিমিট ক্যালকুলেশন (Unlimited কন্ডিশনসহ)
                $newExamLimit = (strtolower($examLimitDefault) == 'unlimited' || strtolower($activeSub->remaining_exam_limit) == 'unlimited') 
                            ? 'Unlimited' 
                            : (int)$activeSub->remaining_exam_limit + (int)$examLimitDefault;

                // বুক লিমিট ক্যালকুলেশন (Unlimited কন্ডিশনসহ)
                $newBookLimit = (strtolower($bookLimitDefault) == 'unlimited' || strtolower($activeSub->remaining_book_limit) == 'unlimited') 
                            ? 'Unlimited' 
                            : (int)$activeSub->remaining_book_limit + (int)$bookLimitDefault;

                $activeSub->update([
                    'end_date'             => $newEndDate,
                    'remaining_exam_limit' => $newExamLimit,
                    'remaining_book_limit' => $newBookLimit, // বুক লিমিট আপডেট
                    'payment_id'           => $payment->id
                ]);
                $message = "Package renewed successfully. New expire date: " . $newEndDate->format('d M, Y');
            } else {
                // --- সুইচ লজিক (ভিন্ন প্যাকেজ) ---
                $activeSub->update(['status' => 'expired']);
                $this->createNewSub($user->id, $package->id, $payment->id, $now, $durationDays, $examLimitDefault, $bookLimitDefault);
                $message = "Switched to " . $package->name . " successfully.";
            }
        } else {
            // --- নতুন সাবস্ক্রিপশন ---
            $this->createNewSub($user->id, $package->id, $payment->id, $now, $durationDays, $examLimitDefault, $bookLimitDefault);
            $message = "Package purchased successfully.";
        }

        DB::commit();

        return response()->json([
            'status'  => true,
            'message' => $message,
            'data'    => $user->load('activeSubscription.package')
        ], 200);

    } catch (Exception $e) {
        DB::rollBack();
        Log::error("App Purchase Failed: " . $e->getMessage());
        return response()->json([
            'status'  => false,
            'message' => 'Transaction failed: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * হেল্পার মেথড: নতুন সাবস্ক্রিপশন এন্ট্রি
     */
    private function createNewSub($userId, $packageId, $paymentId, $now, $days, $examLimit, $bookLimit)
{
    return UserSubscription::create([
        'user_id'              => $userId,
        'package_id'           => $packageId,
        'payment_id'           => $paymentId,
        'start_date'           => $now,
        'end_date'             => $now->copy()->addDays($days),
        'remaining_exam_limit' => $examLimit,
        'remaining_book_limit' => $bookLimit, // নতুন কলাম
        'status'               => 'active'
    ]);
}

    public function paymentMethods()
{
    // স্ট্যাটিক লিস্ট যা অ্যাপের পেমেন্ট স্ক্রিনে দেখানো হবে
    $methods = [
        [
            'slug' => 'bkash',
            'name' => 'BKash',
            'icon' => asset('public/bkash.png'), // আপনার ইমেজ পাথ অনুযায়ী
            
        ],
        [
            'slug' => 'nagad',
            'name' => 'Nagad',
            'icon' => asset('public/nagad.png'),
           
        ],
        [
            'slug' => 'rocket',
            'name' => 'Rocket',
            'icon' => asset('public/rocket.png'),
        ],
        [
            'slug' => 'bank',
            'name' => 'Bank Transfer',
            'icon' => asset('public/bank.png'),
        ]
    ];

    return response()->json([
        'status' => true,
        'message' => 'Payment methods retrieved successfully',
        'data' => $methods
    ], 200);
}

/**
 * অ্যাপের জন্য ডাইনামিক পেমেন্ট মেথড এবং ইন্সট্রাকশন লিস্ট
 */
public function paymentMethodsins()
{
    try {
        $methods = DB::table('payment_settings')
            ->where('status', 1)
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Payment instruction retrieved successfully',
            'data' => $methods->map(function($item) {
                return [
                    'id' => $item->id,
                    'provider' => ucfirst($item->provider),
                    'number' => $item->number,
                    'type' => $item->type,
                    'instruction' => $item->instruction,
                    'logo' => asset('public/assets/images/payment/' . strtolower($item->provider) . '.png')
                ];
            })
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to load instructions: ' . $e->getMessage()
        ], 500);
    }
}


/**
 * ইউজারের কেনা সকল প্যাকেজের হিস্ট্রি (Subscriptions)
 */
public function subscriptionHistory(Request $request)
{
    try {
        $user = $request->user();
        
        // ইউজারের সকল সাবস্ক্রিপশন এবং সাথে প্যাকেজ ইনফো লোড করা হচ্ছে
        $subscriptions = UserSubscription::with('package')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Subscription history retrieved successfully',
            'data' => $subscriptions
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to fetch subscription history'
        ], 500);
    }
}

/**
 * ইউজারের পেমেন্ট হিস্ট্রি
 */
public function paymentHistory(Request $request)
{
    try {
        $user = $request->user();
        
        // ইউজারের সকল পেমেন্ট এবং সাথে কোন প্যাকেজের জন্য পেমেন্ট তা লোড করা হচ্ছে
        $payments = Payment::with('package')
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'message' => 'Payment history retrieved successfully',
            'data' => $payments
        ], 200);

    } catch (Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to fetch payment history'
        ], 500);
    }
}
}