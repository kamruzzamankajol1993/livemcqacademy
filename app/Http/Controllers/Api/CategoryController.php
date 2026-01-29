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
            $categories = Category::where('status', 1)
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
                if (!empty($cat->image)) {
                    if (!str_contains($cat->image, 'http')) {
                        $cat->image = asset('public/'.$cat->image);
                    }
                } else {
                    $cat->image = null;
                }
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
    public function getCategoriesByFeature($id) // এখানে Slug এর বদলে ID নেওয়া হচ্ছে
    {
        try {
            // ফিচার চেক করা (ID দিয়ে)
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

            $categories->transform(function ($cat) {
                if (!empty($cat->image)) {
                    if (!str_contains($cat->image, 'http')) {
                        $cat->image = asset('public/',$cat->image);
                    }
                } else {
                    $cat->image = null;
                }
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