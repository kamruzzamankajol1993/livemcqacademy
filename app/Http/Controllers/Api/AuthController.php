<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Customer Registration API
     */
    /**
 * ধাপ ১: প্রাথমিক রেজিস্ট্রেশন (নাম, ইমেইল, ফোন, পাসওয়ার্ড)
 */
public function register(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'     => 'required|string|max:255',
        'email'    => 'required|string|email|max:255|unique:users',
        'phone'    => 'nullable|string|max:20|unique:users',
        'password' => 'required|string|min:6|confirmed',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
    }

    try {
        DB::beginTransaction();

        $customer = Customer::create([
            'name'     => $request->name,
            'phone'    => $request->phone ?? null,
            'email'    => $request->email,
            'status'   => 1, 
            'slug'     => \Illuminate\Support\Str::slug($request->name) . '-' . time(),
        ]);

        $user = User::create([
            'name'         => $request->name,
            'phone'        => $request->phone ?? null,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'viewpassword' => $request->password,
            'customer_id'  => $customer->id,
            'user_type'    => 2, // Customer type
            'status'       => 1,
        ]);

        $customer->update(['user_id' => $user->id]);

        DB::commit();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status'  => true,
            'message' => 'Step 1: Registration successful. Please select your class.',
            'data'    => ['user' => $user, 'token' => $token]
        ], 201);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json(['status' => false, 'message' => 'Registration failed: ' . $e->getMessage()], 500);
    }
}

/**
 * ধাপ ২ ও ৩: ক্লাস এবং ডিপার্টমেন্ট আপডেট করা
 */
/**
 * ধাপ ২ ও ৩: ক্লাস এবং ডিপার্টমেন্ট আপডেট করা
 */
public function updateAcademicInfo(Request $request)
{
    // ১. ইনপুট ভ্যালিডেশন
    $validator = Validator::make($request->all(), [
        'class_id'      => 'required|exists:school_classes,id',
        'department_id' => 'nullable|exists:class_departments,id',
    ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
    }

    try {
        $user = $request->user();
        
        // ২. ইউজার টেবিলে ক্লাস এবং ডিপার্টমেন্ট আপডেট
        $user->update([
            'class_id'      => $request->class_id,
            'department_id' => $request->department_id
        ]);

        // ৩. কাস্টমার টেবিলে সিঙ্ক রাখা (যদি কলাম থাকে)
        if ($user->customer) {
            $user->customer->update([
                'class_id'      => $request->class_id,
                'department_id' => $request->department_id
            ]);
        }

        // ৪. নতুন ডাটা রেসপন্সে পাঠানোর জন্য রিলেশনগুলো রিফ্রেশ করা
        $user->load(['schoolClass', 'department']);

        // ৫. বর্তমান সেশনের টোকেন সংগ্রহ (যেহেতু এটি একটি প্রটেক্টেড রিকোয়েস্ট)
        $token = $request->bearerToken();

        // ৬. আপনার চাহিদা অনুযায়ী কাস্টম রেসপন্স
        return response()->json([
            'status'  => true,
            'message' => 'Academic info updated successfully',
            'data'    => [
                'user'  => [
                    'id'                 => $user->id,
                    'name'               => $user->name,
                    'email'              => $user->email,
                    'phone'              => $user->phone,
                    'image'              => $user->image ? asset($user->image) : null,
                    
                    // ক্লাস তথ্য
                    'class_id'           => $user->class_id,
                    'class_name'         => $user->schoolClass ? $user->schoolClass->name_en : null,
                    'class_name_bn'      => $user->schoolClass ? $user->schoolClass->name_bn : null,
                    
                    // ডিপার্টমেন্ট তথ্য
                    'department_id'      => $user->department_id,
                    'department_name'    => $user->department ? $user->department->name_en : null,
                    'department_name_bn' => $user->department ? $user->department->name_bn : null,
                    
                    'status'             => $user->status,
                ],
                'token' => $token
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false, 
            'message' => 'Failed to update info: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Customer Login API
     */
    /**
 * Customer Login API
 * ক্লাস এবং ডিপার্টমেন্টের তথ্যসহ সম্পূর্ণ লগইন ফাংশন
 */
public function login(Request $request)
{
    // ১. ভ্যালিডেশন
    $validator = Validator::make($request->all(), [
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false, 
            'errors' => $validator->errors()
        ], 422);
    }

    // ২. ইউজার চেক করা এবং রিলেশন লোড করা (schoolClass ও department)
    $user = User::with(['schoolClass', 'department'])
                ->where('email', $request->email)
                ->first();

    // ৩. পাসওয়ার্ড যাচাই
    if (!$user || !Hash::check($request->password, $user->password)) {
        return response()->json([
            'status'  => false,
            'message' => 'Invalid credentials'
        ], 401);
    }

    // ৪. টোকেন তৈরি (Sanctum)
    $token = $user->createToken('auth_token')->plainTextToken;

    // ৫. রেসপন্স প্রদান
    return response()->json([
        'status'  => true,
        'message' => 'Login successful',
        'data'    => [
            'user'  => [
                'id'              => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'phone'           => $user->phone,
                'image'           => $user->image ? asset($user->image) : null,
                
                // ক্লাস তথ্য
                'class_id'        => $user->class_id,
                'class_name'      => $user->schoolClass ? $user->schoolClass->name_en : null,
                'class_name_bn'   => $user->schoolClass ? $user->schoolClass->name_bn : null,
                
                // ডিপার্টমেন্ট তথ্য
                'department_id'   => $user->department_id,
                'department_name' => $user->department ? $user->department->name_en : null,
                'department_name_bn' => $user->department ? $user->department->name_bn : null,
                
                'status'          => $user->status,
            ],
            'token' => $token
        ]
    ], 200);
}

    /**
     * Dashboard / Profile API
     */
   public function dashboard(Request $request)
{
    try {
        // ইউজার এবং তার রিলেটেড ডাটা (সাথে schoolClass এবং department) লোড করা
        $user = $request->user()->load([
            'customer', 
            'schoolClass', 
            'department', // নতুন রিলেশন লোড
            'activeSubscription.package', 
            'subscriptions.package', 
            'payments.package'
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User dashboard data retrieved successfully',
            'data' => [
                'user_info' => [
                    'id'              => $user->id,
                    'name'            => $user->name,
                    'email'           => $user->email,
                    'phone'           => $user->phone,
                    'image'           => $user->image ? asset($user->image) : null,
                    // ক্লাস তথ্য
                    'class_id'        => $user->class_id,
                    'class_name'      => $user->schoolClass ? $user->schoolClass->name_en : null, 
                    // ডিপার্টমেন্ট তথ্য
                    'department_id'   => $user->department_id,
                    'department_name' => $user->department ? $user->department->name_en : null,
                ],
                
                'active_subscription'  => $user->activeSubscription,
                'subscription_history' => $user->subscriptions,
                'payment_history'      => $user->payments,
                'stats' => [
                    'total_packages_bought' => $user->subscriptions()->count(),
                ]
            ]
        ], 200);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Failed to load dashboard: ' . $e->getMessage()
        ], 500);
    }
}
    /**
     * Logout API
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => true,
            'message' => 'Logged out successfully'
        ], 200);
    }


    public function sendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email|exists:users,email']);

        // Generate 6 digit random OTP
        $otpCode = rand(100000, 999999);

        // Save to DB (Update if exists or Create new)
        Otp::updateOrCreate(
            ['email' => $request->email],
            [
                'otp' => $otpCode,
                'expires_at' => Carbon::now()->addMinutes(10) // 10 মিনিট মেয়াদ
            ]
        );

        // Send Email
        try {
            Mail::to($request->email)->send(new OtpMail($otpCode));
            
            return response()->json([
                'status' => true,
                'message' => 'OTP sent to your email.'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Failed to send email. Check mail configuration.'
            ], 500);
        }
    }

    /**
     * 2. Verify OTP (App থেকে OTP সাবমিট করবে)
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:6'
        ]);

        $otpRecord = Otp::where('email', $request->email)->where('otp', $request->otp)->first();

        // Check if OTP matches
        if (!$otpRecord) {
            return response()->json(['status' => false, 'message' => 'Invalid OTP'], 400);
        }

        // Check if OTP expired
        if (Carbon::now()->isAfter($otpRecord->expires_at)) {
            return response()->json(['status' => false, 'message' => 'OTP has expired'], 400);
        }

        return response()->json([
            'status' => true,
            'message' => 'OTP verified successfully. You can change password now.'
        ], 200);
    }

    /**
     * 3. Reset Password (পাসওয়ার্ড চেঞ্জ ফর্ম থেকে)
     * Security Note: এখানেও OTP চেক করা হচ্ছে যাতে কেউ API বাইপাস করতে না পারে।
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'otp'      => 'required|digits:6',
            'password' => 'required|min:6|confirmed'
        ]);

        // Verify OTP again for security
        $otpRecord = Otp::where('email', $request->email)->where('otp', $request->otp)->first();

        if (!$otpRecord || Carbon::now()->isAfter($otpRecord->expires_at)) {
            return response()->json(['status' => false, 'message' => 'Invalid or expired OTP'], 400);
        }

        // Update User Password
        $user = User::where('email', $request->email)->first();
        $user->password = Hash::make($request->password);
        $user->viewpassword = $request->password; // যদি প্লেইন টেক্সট রাখতে চান
        $user->save();

        // Delete OTP after usage
        $otpRecord->delete();

        return response()->json([
            'status' => true,
            'message' => 'Password reset successfully. Please login.'
        ], 200);
    }

    /**
 * 1. Update Profile Request
 */
public function updateProfileRequest(Request $request)
{
    $user = $request->user(); // Authenticated User

    // Validation
    $validator = Validator::make($request->all(), [
            'name'     => 'nullable|string|max:255',
            'email'    => 'nullable|email|unique:users,email,' . $user->id,
            'phone'    => 'nullable|string|max:20|unique:users,phone,' . $user->id, // নিজের আইডি বাদে ইউনিক চেক
            'password' => 'nullable|min:6|confirmed',
        ]);

    if ($validator->fails()) {
        return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
    }

    // Check if sensitive data is being changed
    $isSensitiveChange = false;
    $changes = [];

    // Check Email Change
    if ($request->has('email') && $request->email !== $user->email) {
        $isSensitiveChange = true;
        $changes['email'] = $request->email;
    }

    // Check Phone Change
    if ($request->has('phone') && $request->phone !== $user->phone) {
        $isSensitiveChange = true;
        $changes['phone'] = $request->phone;
    }

    // Check Password Change
    if ($request->has('password') && !empty($request->password)) {
        $isSensitiveChange = true;
        $changes['password'] = Hash::make($request->password);
        $changes['viewpassword'] = $request->password; // যদি প্লেইন রাখতে চান
    }

    // --- CASE 1: Only Name Change (No OTP) ---
    if (!$isSensitiveChange) {
        if ($request->has('name')) {
            $user->name = $request->name;
            $user->save();
            
            // Customer table update (if synced)
            if ($user->customer) {
                $user->customer->update(['name' => $request->name]);
            }

            return response()->json([
                'status' => true,
                'message' => 'Profile updated successfully (Name only).',
                'otp_required' => false
            ]);
        }
        
        return response()->json(['status' => false, 'message' => 'No changes detected.']);
    }

    // --- CASE 2: Sensitive Data Change (OTP Required) ---
    
    // নামও যদি সাথে থাকে, সেটাও Payload এ রেখে দিব
    if ($request->has('name')) {
        $changes['name'] = $request->name;
    }

    // Generate OTP
    $otpCode = rand(100000, 999999);

    // Save OTP & The Requested Changes (Payload) to DB
    Otp::updateOrCreate(
        ['email' => $user->email], // বর্তমান ইমেইলে OTP যাবে
        [
            'otp' => $otpCode,
            'expires_at' => Carbon::now()->addMinutes(10),
            'payload' => $changes // নতুন ডাটাগুলো এখানে জমা থাকবে
        ]
    );

    // Send OTP to Current Email
    try {
        Mail::to($user->email)->send(new OtpMail($otpCode));
        
        return response()->json([
            'status' => true,
            'message' => 'OTP sent to your email for verification.',
            'otp_required' => true
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status' => false,
            'message' => 'Failed to send OTP.'
        ], 500);
    }
}

/**
 * 2. Confirm Profile Update (Submit OTP)
 */
public function confirmProfileUpdate(Request $request)
{
    $request->validate([
        'otp' => 'required|digits:6'
    ]);

    $user = $request->user();

    // Find OTP record for this user
    $otpRecord = Otp::where('email', $user->email)
                    ->where('otp', $request->otp)
                    ->first();

    // Verify OTP
    if (!$otpRecord) {
        return response()->json(['status' => false, 'message' => 'Invalid OTP'], 400);
    }

    if (Carbon::now()->isAfter($otpRecord->expires_at)) {
        return response()->json(['status' => false, 'message' => 'OTP has expired'], 400);
    }

    // --- Apply Changes from Payload ---
    $changes = $otpRecord->payload;

    if (!$changes) {
        return response()->json(['status' => false, 'message' => 'No pending changes found.'], 400);
    }

    // Update User Table
    $user->update($changes);

    // Update Customer Table (Syncing info)
    if ($user->customer) {
        $customerData = [];
        if (isset($changes['name'])) $customerData['name'] = $changes['name'];
        if (isset($changes['email'])) $customerData['email'] = $changes['email'];
        if (isset($changes['phone'])) $customerData['phone'] = $changes['phone'];
        if (isset($changes['password'])) $customerData['password'] = $request->password; // Customer মডেলে মিউটেটর থাকলে প্লেইন পাসওয়ার্ড যাবে

        if (!empty($customerData)) {
            $user->customer->update($customerData);
        }
    }

    // Delete OTP record
    $otpRecord->delete();

    return response()->json([
        'status' => true,
        'message' => 'Profile updated successfully.'
    ]);
}
}