<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Feature;

class FeatureController extends Controller
{
    public function index()
    {
        try {
            // ১. শুধুমাত্র অ্যাক্টিভ ফিচারগুলো নিয়ে আসা
            // আপনি চাইলে parent-child রিলেশনসহ আনতে পারেন: Feature::with('children')->where('status', 1)...
            $features = Feature::where('status', 1)
                ->orderBy('serial', 'asc') // বা আপনার প্রয়োজন অনুযায়ী order
                ->get();

            // ২. ইমেজের ফুল URL পাথ তৈরি করা (API তে ইমেজ দেখানোর জন্য জরুরি)
            $features->transform(function ($feature) {
                if (!empty($feature->image)) {
                    // যদি ইমেজে http না থাকে (অর্থাৎ লোকাল পাথ), তাহলে asset() বা url() দিয়ে ফুল লিংক বানানো
                    if (!str_contains($feature->image, 'http')) {
                        $feature->image = asset('public/'.$feature->image); 
                    }
                } else {
                    $feature->image = null; // ইমেজ না থাকলে নাল
                }
                return $feature;
            });

            return response()->json([
                'status'  => true,
                'message' => 'Feature list retrieved successfully',
                'data'    => $features
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Something went wrong: ' . $e->getMessage()
            ], 500);
        }
    }
}