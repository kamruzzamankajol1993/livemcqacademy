<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Feature;

class CategoryController extends Controller
{
    // ১. সকল ক্যাটাগরি লিস্ট
    public function index()
    {
        try {
            // with('feature:...') ব্যবহার করে রিলেশন লোড করা হলো
            $categories = Category::with('feature:id,english_name,bangla_name')
                ->where('status', 1)
                ->select(
                    'id', 
                    'parent_id', 
                    'feature_id', 
                    'english_name', 
                    'bangla_name', 
                    'slug', 
                    'color', 
                    'image', 
                    'serial', 
                    'status'
                )
                ->orderBy('serial', 'asc')
                ->get();

            $categories->transform(function ($cat) {
                // Image Path Processing
                if (!empty($cat->image)) {
                    if (!str_contains($cat->image, 'http')) {
                        $cat->image = asset('public/' . $cat->image);
                    }
                } else {
                    $cat->image = null;
                }

                // Feature Name সংযুক্ত করা হলো
                $cat->feature_name_en = $cat->feature ? $cat->feature->english_name : null;
                $cat->feature_name_bn = $cat->feature ? $cat->feature->bangla_name : null;

                // রিলেশন অবজেক্ট হাইড করতে চাইলে নিচের লাইনটি আনকমেন্ট করুন
                // unset($cat->feature); 

                return $cat;
            });

            return response()->json([
                'status' => true,
                'message' => 'Category list retrieved successfully',
                'data' => $categories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // ২. ফিচার অনুযায়ী ক্যাটাগরি লিস্ট (ID দিয়ে)
    public function getCategoriesByFeature($id)
    {
        try {
            // ফিচার চেক করা
            $feature = Feature::where('id', $id)->where('status', 1)->first();

            if (!$feature) {
                return response()->json([
                    'status' => false,
                    'message' => 'Feature not found'
                ], 404);
            }

            // ওই ফিচারের আন্ডারে থাকা ক্যাটাগরিগুলো আনা
            $categories = Category::where('feature_id', $feature->id)
                ->where('status', 1)
                ->select(
                    'id', 
                    'parent_id', 
                    'feature_id', 
                    'english_name', 
                    'bangla_name', 
                    'slug', 
                    'color', 
                    'image', 
                    'serial', 
                    'status'
                )
                ->orderBy('serial', 'asc')
                ->get();

            // $feature ভেরিয়েবলটি use() এর মাধ্যমে লুপের ভেতরে পাঠানো হলো
            $categories->transform(function ($cat) use ($feature) {
                // Image Path Processing
                if (!empty($cat->image)) {
                    if (!str_contains($cat->image, 'http')) {
                        $cat->image = asset('public/' . $cat->image); // Fixed comma to dot
                    }
                } else {
                    $cat->image = null;
                }

                // Feature Name সংযুক্ত করা হলো (উপরের $feature থেকে)
                $cat->feature_name_en = $feature->english_name;
                $cat->feature_name_bn = $feature->bangla_name;

                return $cat;
            });

            return response()->json([
                'status' => true,
                'message' => 'Categories retrieved for feature ID: ' . $feature->id,
                'data' => $categories
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}