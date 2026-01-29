<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Section;

class SectionController extends Controller
{
    /**
     * ১. সকল সেকশন লিস্ট (All Section List)
     * URL: /api/sections
     */
    public function index()
    {
        try {
            // category_id সহ ডাটা আনা হচ্ছে
            $sections = Section::where('status', 1)
                ->select(
                    'id', 
                    'name_en', 
                    'name_bn', 
                    'slug', 
                    'category_id', // Added
                    'class_id', 
                    'subject_id', 
                    'serial', 
                    'status'
                )
                ->orderBy('serial', 'asc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'All sections retrieved successfully',
                'data' => $sections
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ২. ফিল্টার সেকশন (Filter Sections)
     * এটি category_id, class_id অথবা subject_id দিয়ে ফিল্টার করবে।
     * * * URL Examples:
     * - Category Wise: /api/sections/filter?category_id=2
     * - Class Wise:    /api/sections/filter?class_id=1
     * - Subject Wise:  /api/sections/filter?subject_id=5
     * - Combined:      /api/sections/filter?category_id=2&class_id=1
     */
    public function filterSections(Request $request)
    {
        try {
            $query = Section::where('status', 1);

            // ১. ক্যাটাগরি ফিল্টার (NEW)
            if ($request->has('category_id') && !empty($request->category_id)) {
                $query->where('category_id', $request->category_id);
            }

            // ২. ক্লাস ফিল্টার
            if ($request->has('class_id') && !empty($request->class_id)) {
                $query->where('class_id', $request->class_id);
            }

            // ৩. সাবজেক্ট ফিল্টার
            if ($request->has('subject_id') && !empty($request->subject_id)) {
                $query->where('subject_id', $request->subject_id);
            }

            // ডাটা সিলেকশন (category_id সহ)
            $sections = $query->select(
                    'id', 
                    'name_en', 
                    'name_bn', 
                    'slug', 
                    'category_id', // Added
                    'class_id', 
                    'subject_id', 
                    'serial', 
                    'status'
                )
                ->orderBy('serial', 'asc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Sections retrieved successfully based on filters.',
                'data' => $sections
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}