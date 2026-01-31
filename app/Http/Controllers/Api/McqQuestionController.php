<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\McqQuestion;

class McqQuestionController extends Controller
{
    /**
     * MCQ List API with Filters & Pagination
     * * URL: /api/mcqs
     * Method: GET
     * * Supported Parameters:
     * ?page=1
     * ?per_page=20
     * ?class_id=1
     * ?subject_id=5
     * ?chapter_id=10
     * ?topic_id=2
     * ?board_id=3
     * ?year_id=2024
     * ?institute_id=4
     * ?search=keyword
     */
    public function index(Request $request)
    {
        try {
            // ১. সব রিলেশন লোড করা (Eager Loading)
            $query = McqQuestion::with([
                'category:id,name,english_name,bangla_name', 
                'class:id,name_en,name_bn', 
                'subject:id,name_en,name_bn', 
                'chapter:id,name_en,name_bn', 
                'topic:id,name_en,name_bn',
                'board:id,name_en,name_bn',
                'institute:id,name_en,name_bn',
                'academicYear:id,name_en,name_bn',
                'department:id,name_en,name_bn'
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

            // ইয়ার ফিল্টার (Academic Year)
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
            $perPage = $request->input('per_page', 20);
            $mcqs = $query->latest()->paginate($perPage);

            // ৪. ডাটা ট্রান্সফর্ম করে নামগুলো মেইন অবজেক্টে নিয়ে আসা
            $mcqs->getCollection()->transform(function ($item) {
                // Class Name
                $item->class_name_en = $item->class->name_en ?? null;
                $item->class_name_bn = $item->class->name_bn ?? null;

                // Subject Name
                $item->subject_name_en = $item->subject->name_en ?? null;
                $item->subject_name_bn = $item->subject->name_bn ?? null;

                // Chapter Name
                $item->chapter_name_en = $item->chapter->name_en ?? null;
                $item->chapter_name_bn = $item->chapter->name_bn ?? null;

                // Topic Name
                $item->topic_name_en = $item->topic->name_en ?? null;
                $item->topic_name_bn = $item->topic->name_bn ?? null;

                // Board Name
                $item->board_name_en = $item->board->name_en ?? null;
                $item->board_name_bn = $item->board->name_bn ?? null;
                
                // Institute Name
                $item->institute_name_en = $item->institute->name_en ?? null;
                $item->institute_name_bn = $item->institute->name_bn ?? null;

                // Department Name
                $item->department_name_en = $item->department->name_en ?? null;
                $item->department_name_bn = $item->department->name_bn ?? null;

                // Year Name
                $item->year_name_en = $item->academicYear->name_en ?? null;
                $item->year_name_bn = $item->academicYear->name_bn ?? null;

                // Category Name
                if($item->category) {
                    $item->category_name_en = $item->category->english_name ?? $item->category->name;
                    $item->category_name_bn = $item->category->bangla_name;
                } else {
                    $item->category_name_en = null;
                    $item->category_name_bn = null;
                }

                // Optional: রিলেশন অবজেক্টগুলো হাইড করা (Response ক্লিন রাখার জন্য)
                // আপনি চাইলে এই লাইনটি কমেন্ট করে রাখতে পারেন যদি নেস্টেড অবজেক্টও দরকার হয়
               // unset($item->class, $item->subject, $item->chapter, $item->topic, $item->board, $item->institute, $item->department, $item->academicYear, $item->category);

                return $item;
            });

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
     * Single MCQ Details
     * URL: /api/mcqs/{id}
     */
   public function show($id)
    {
        try {
            // ১. সব রিলেশন লোড করা (Eager Loading)
            $mcq = McqQuestion::with([
                'category', 
                'class', 
                'subject', 
                'chapter', 
                'topic', 
                'board', 
                'institute', 
                'academicYear', 
                'department'
            ])->find($id);

            // চেক করা ডাটা আছে কিনা
            if (!$mcq) {
                return response()->json(['status' => false, 'message' => 'MCQ not found'], 404);
            }

            // ২. নামগুলো মেইন অবজেক্টে নিয়ে আসা (Flattening Data)
            
            // Class Name
            $mcq->class_name_en = $mcq->class->name_en ?? null;
            $mcq->class_name_bn = $mcq->class->name_bn ?? null;

            // Subject Name
            $mcq->subject_name_en = $mcq->subject->name_en ?? null;
            $mcq->subject_name_bn = $mcq->subject->name_bn ?? null;

            // Chapter Name
            $mcq->chapter_name_en = $mcq->chapter->name_en ?? null;
            $mcq->chapter_name_bn = $mcq->chapter->name_bn ?? null;

            // Topic Name
            $mcq->topic_name_en = $mcq->topic->name_en ?? null;
            $mcq->topic_name_bn = $mcq->topic->name_bn ?? null;

            // Board Name
            $mcq->board_name_en = $mcq->board->name_en ?? null;
            $mcq->board_name_bn = $mcq->board->name_bn ?? null;
            
            // Institute Name
            $mcq->institute_name_en = $mcq->institute->name_en ?? null;
            $mcq->institute_name_bn = $mcq->institute->name_bn ?? null;

            // Department Name
            $mcq->department_name_en = $mcq->department->name_en ?? null;
            $mcq->department_name_bn = $mcq->department->name_bn ?? null;

            // Year Name (Academic Year)
            $mcq->year_name_en = $mcq->academicYear->name_en ?? null;
            $mcq->year_name_bn = $mcq->academicYear->name_bn ?? null;

            // Category Name
            if($mcq->category) {
                $mcq->category_name_en = $mcq->category->english_name ?? $mcq->category->name;
                $mcq->category_name_bn = $mcq->category->bangla_name;
            } else {
                $mcq->category_name_en = null;
                $mcq->category_name_bn = null;
            }

            // ৩. Optional: রিলেশন অবজেক্টগুলো হাইড করা (Clean Response)
            // আপনি যদি নেস্টেড অবজেক্ট (যেমন: $mcq->class->name) না চান, তবে নিচের লাইনটি রাখুন।
            // unset(
            //     $mcq->class, 
            //     $mcq->subject, 
            //     $mcq->chapter, 
            //     $mcq->topic, 
            //     $mcq->board, 
            //     $mcq->institute, 
            //     $mcq->department, 
            //     $mcq->academicYear, 
            //     $mcq->category
            // );

            return response()->json([
                'status' => true,
                'message' => 'MCQ details retrieved successfully',
                'data' => $mcq
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false, 
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}