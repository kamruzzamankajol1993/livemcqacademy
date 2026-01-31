<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\Customer;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SocialAuthController extends Controller
{
    /**
     * 1. Mobile Facebook Login
     * অ্যাপ ফেইসবুক SDK থেকে পাওয়া 'access_token' পাঠাবে।
     */
    public function loginWithFacebook(Request $request)
    {
        // ভ্যালিডেশন
        $validator = Validator::make($request->all(), [
            'access_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // ফেইসবুক টোকেন ভেরিফাই করে ইউজার ডাটা আনা
            $socialUser = Socialite::driver('facebook')->stateless()->userFromToken($request->access_token);

            // ডাটাবেজে ইউজার হ্যান্ডেল করা (খুঁজে বের করা বা তৈরি করা)
            $user = $this->findOrCreateUser($socialUser, 'facebook');

            // টোকেন জেনারেট করা
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful with Facebook',
                'data' => [
                    'user' => $user->load('customer'),
                    'token' => $token
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Facebook Login Failed: ' . $e->getMessage()
            ], 401);
        }
    }

    /**
     * 2. Mobile Google Login
     * অ্যাপ গুগল SDK থেকে পাওয়া 'access_token' পাঠাবে।
     */
    public function loginWithGoogle(Request $request)
    {
        // ভ্যালিডেশন
        $validator = Validator::make($request->all(), [
            'access_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'errors' => $validator->errors()], 422);
        }

        try {
            // গুগল টোকেন ভেরিফাই করে ইউজার ডাটা আনা
            $socialUser = Socialite::driver('google')->stateless()->userFromToken($request->access_token);

            // ডাটাবেজে ইউজার হ্যান্ডেল করা
            $user = $this->findOrCreateUser($socialUser, 'google');

            // টোকেন জেনারেট করা
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'status' => true,
                'message' => 'Login successful with Google',
                'data' => [
                    'user' => $user->load('customer'),
                    'token' => $token
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Google Login Failed: ' . $e->getMessage()
            ], 401);
        }
    }

    /**
     * Helper Method: Find or Create User
     * এই মেথডটি ফেইসবুক এবং গুগল উভয়ের জন্যই কাজ করবে।
     */
    private function findOrCreateUser($socialUser, $provider)
    {
        // ১. সোশ্যাল আইডি দিয়ে ইউজার খোঁজা
        $existingUser = User::where($provider . '_id', $socialUser->getId())->first();

        if ($existingUser) {
            return $existingUser;
        }

        // ২. ইমেইল দিয়ে ইউজার খোঁজা (যদি আগে ম্যানুয়ালি রেজিস্ট্রেশন করে থাকে)
        $userByEmail = User::where('email', $socialUser->getEmail())->first();

        if ($userByEmail) {
            // ইমেইল মিলে গেলে সোশ্যাল আইডি আপডেট করে দেওয়া
            $userByEmail->update([
                $provider . '_id' => $socialUser->getId(),
                'avatar' => $socialUser->getAvatar()
            ]);
            return $userByEmail;
        }

        // ৩. নতুন ইউজার এবং কাস্টমার তৈরি করা (Transaction সহ)
        return DB::transaction(function () use ($socialUser, $provider) {
            
            // কাস্টমার টেবিল এ ডাটা তৈরি
            $customer = Customer::create([
                'name'   => $socialUser->getName(),
                'email'  => $socialUser->getEmail(),
                'phone'  => null, // সোশ্যাল লগইনে ফোন নম্বর নাও আসতে পারে
                'status' => 'active',
                'slug'   => Str::slug($socialUser->getName()) . '-' . time(),
            ]);

            // ইউজার টেবিল এ ডাটা তৈরি
            return User::create([
                'name'          => $socialUser->getName(),
                'email'         => $socialUser->getEmail(),
                'password'      => null, // সোশ্যাল লগইনে পাসওয়ার্ড থাকে না
                'viewpassword'  => null,
                'customer_id'   => $customer->id,
                'user_type'     => 2, // 2 = Customer
                'type'          => 'customer',
                'status'        => 1,
                $provider . '_id' => $socialUser->getId(), // facebook_id or google_id
                'avatar'        => $socialUser->getAvatar(),
            ]);
        });
    }
}