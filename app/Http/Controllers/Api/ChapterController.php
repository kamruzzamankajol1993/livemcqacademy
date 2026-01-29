<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Chapter;

class ChapterController extends Controller
{
    /**
     * ১. সকল চ্যাপ্টার লিস্ট (All Chapter List)
     * URL: /api/chapters
     */
    public function index()
    {
        try {
            $chapters = Chapter::where('status', 1)
                ->select(
                    'id', 
                    'name_en', 
                    'name_bn', 
                    'slug', 
                    'class_id', 
                    'subject_id', 
                    'section_id', // Optional
                    'serial', 
                    'status'
                )
                ->orderBy('serial', 'asc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'All chapters retrieved successfully',
                'data' => $chapters
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ২. ফিল্টার চ্যাপ্টার (Filter Chapters)
     * এই API টি subject_id (Primary) অথবা section_id দিয়ে ফিল্টার করবে।
     * * URL Examples:
     * - Subject Wise: /api/chapters/filter?subject_id=5
     * - Subject & Section Wise: /api/chapters/filter?subject_id=5&section_id=2
     * - Class Wise (Optional): /api/chapters/filter?class_id=1
     */
    public function filterChapters(Request $request)
    {
        try {
            $query = Chapter::where('status', 1);

            // ১. সাবজেক্ট ফিল্টার (Primary Filter)
            if ($request->has('subject_id') && !empty($request->subject_id)) {
                $query->where('subject_id', $request->subject_id);
            }

            // ২. সেকশন ফিল্টার (Optional - যদি অ্যাপ থেকে পাঠানো হয়)
            if ($request->has('section_id') && !empty($request->section_id)) {
                $query->where('section_id', $request->section_id);
            }

            // ৩. ক্লাস ফিল্টার (Optional - হায়ারার্কি মেইনটেইন করতে চাইলে)
            if ($request->has('class_id') && !empty($request->class_id)) {
                $query->where('class_id', $request->class_id);
            }

            // ডাটা সিলেকশন ও অর্ডারিং
            $chapters = $query->select(
                    'id', 
                    'name_en', 
                    'name_bn', 
                    'slug', 
                    'class_id', 
                    'subject_id', 
                    'section_id', 
                    'serial', 
                    'status'
                )
                ->orderBy('serial', 'asc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Chapters retrieved successfully based on filters.',
                'data' => $chapters
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}