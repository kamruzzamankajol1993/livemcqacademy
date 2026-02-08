<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SchoolClass;
use App\Models\Category;

class ClassController extends Controller
{
    // ১. সকল ক্লাসের লিস্ট (All Class List)
    public function index()
    {
        try {
            $classes = SchoolClass::where('status', 1)
                ->select(
                    'id',
                    'name_en',
                    'name_bn',
                    'slug',
                    'image',
                    'color',
                    'serial',
                    'status'
                )
                ->orderBy('serial', 'asc')
                ->get();

            // ইমেজ URL প্রসেসিং
            $classes->transform(function ($cls) {
                if (!empty($cls->image)) {
                    if (!str_contains($cls->image, 'http')) {
                        $cls->image = asset('public/'.$cls->image);
                    }
                } else {
                    $cls->image = null;
                }
                return $cls;
            });

            return response()->json([
                'status' => true,
                'message' => 'Class list retrieved successfully',
                'data' => $classes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // ২. ক্যাটাগরি অনুযায়ী ক্লাস লিস্ট (Category Wise Class List - By ID)
    public function getClassesByCategory(Request $request)
    {
        $categoryId = $request->id; // URL থেকে ID প্যারামিটার নেওয়া হচ্ছে
        try {
            // ক্যাটাগরি চেক করা
            $category = Category::where('id', $categoryId)->where('status', 1)->first();

            if (!$category) {
                return response()->json([
                    'status' => false,
                    'message' => 'Category not found'
                ], 404);
            }

            // ওই ক্যাটাগরির সাথে অ্যাসাইন করা ক্লাসগুলো আনা
            // SchoolClass মডেলে categories() রিলেশনশিপ (belongsToMany) থাকতে হবে
            $classes = SchoolClass::where('status', 1)
                ->whereHas('categories', function($q) use ($categoryId) {
                    $q->where('categories.id', $categoryId);
                })
                ->select(
                    'school_classes.id',
                    'school_classes.name_en',
                    'school_classes.name_bn',
                    'school_classes.slug',
                    'school_classes.image',
                    'school_classes.color',
                    'school_classes.serial',
                    'school_classes.status'
                )
                ->orderBy('serial', 'asc')
                ->get();

            // ইমেজ URL প্রসেসিং
            $classes->transform(function ($cls) {
                if (!empty($cls->image)) {
                    if (!str_contains($cls->image, 'http')) {
                        $cls->image = asset('public/'.$cls->image);
                    }
                } else {
                    $cls->image = null;
                }
                return $cls;
            });

            return response()->json([
                'status' => true,
                'message' => 'Classes retrieved for category ID: ' . $category->id,
                'data' => $classes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}