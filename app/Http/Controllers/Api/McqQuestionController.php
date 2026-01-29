<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\McqQuestion;

class McqQuestionController extends Controller
{
    /**
     * MCQ List API with Filters & Pagination
     * URL: /api/mcqs
     * * Supported Parameters:
     * ?page=1
     * ?per_page=20
     * ?class_id=1
     * ?subject_id=5
     * ?chapter_id=10
     * ?topic_id=2
     * ?board_id=3
     * ?year_id=2024
     * ?search=keyword
     */
    public function index(Request $request)
    {
        try {
            // ১. রিলেশনসহ কুয়েরি শুরু (Eager Loading for Performance)
            $query = McqQuestion::with([
                'class:id,name_en,name_bn', 
                'subject:id,name_en,name_bn', 
                'chapter:id,name_en,name_bn', 
                'topic:id,name_en,name_bn',
                'board:id,name_en,name_bn',
                'institute:id,name_en,name_bn'
            ])->where('status', 1);

            // ২. ডাইনামিক ফিল্টারিং (Dynamic Filtering)
            
            // ক্লাস ফিল্টার
            if ($request->has('class_id') && !empty($request->class_id)) {
                $query->where('class_id', $request->class_id);
            }

            // সাবজেক্ট ফিল্টার
            if ($request->has('subject_id') && !empty($request->subject_id)) {
                $query->where('subject_id', $request->subject_id);
            }

            // চ্যাপ্টার ফিল্টার
            if ($request->has('chapter_id') && !empty($request->chapter_id)) {
                $query->where('chapter_id', $request->chapter_id);
            }

            // টপিক ফিল্টার
            if ($request->has('topic_id') && !empty($request->topic_id)) {
                $query->where('topic_id', $request->topic_id);
            }

            // বোর্ড ফিল্টার
            if ($request->has('board_id') && !empty($request->board_id)) {
                $query->where('board_id', $request->board_id);
            }

            // ইয়ার ফিল্টার
            if ($request->has('year_id') && !empty($request->year_id)) {
                $query->where('year_id', $request->year_id);
            }
            
            // ইনস্টিটিউট ফিল্টার
            if ($request->has('institute_id') && !empty($request->institute_id)) {
                $query->where('institute_id', $request->institute_id);
            }

            // সার্চ ফিল্টার (প্রশ্ন খোঁজার জন্য)
            if ($request->has('search') && !empty($request->search)) {
                $searchTerm = $request->search;
                $query->where('question', 'LIKE', "%{$searchTerm}%");
            }

            // ৩. পেজিনেশন (Pagination)
            // ডিফল্ট ২০টি করে ডাটা দিবে, অ্যাপ থেকে per_page পাঠালে সেটা নিবে
            $perPage = $request->input('per_page', 20);
            
            // Random Order চাইলে: $query->inRandomOrder() ব্যবহার করতে পারেন
            // এখানে লেটেস্ট ডাটা আগে রাখা হয়েছে
            $mcqs = $query->latest()->paginate($perPage);

            return response()->json([
                'status' => true,
                'message' => 'MCQ list retrieved successfully',
                'data' => $mcqs
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Single MCQ Details (Optional)
     */
    public function show($id)
    {
        try {
            $mcq = McqQuestion::with([
                'class', 'subject', 'chapter', 'topic', 'board', 'institute'
            ])->find($id);

            if (!$mcq) {
                return response()->json(['status' => false, 'message' => 'MCQ not found'], 404);
            }

            return response()->json([
                'status' => true,
                'data' => $mcq
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['status' => false, 'message' => 'Error'], 500);
        }
    }
}