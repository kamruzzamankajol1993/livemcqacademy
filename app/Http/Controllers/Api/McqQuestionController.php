<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\McqQuestion;
use App\Models\Institute;
use App\Models\Board;
use App\Models\Section;
class McqQuestionController extends Controller
{
    /**
     * MCQ List API with Filters, Pagination & Bilingual Support
     */
    public function index(Request $request)
    {
        try {
            // ১. সব রিলেশন লোড করা (Eager Loading)
            $query = McqQuestion::with([
                'category:id,english_name,bangla_name', 
                'class:id,name_en,name_bn', 
                'subject:id,name_en,name_bn', 
                'chapter:id,name_en,name_bn', 
                'topic:id,name_en,name_bn',
                'section:id,name_en,name_bn', // নতুন সেকশন যুক্ত করা হয়েছে
                'department:id,name_en,name_bn'
            ])->where('status', 1);

            // ২. ডাইনামিক ফিল্টারিং
            if ($request->filled('class_id')) {
                $query->where('class_id', $request->class_id);
            }
            if ($request->filled('subject_id')) {
                $query->where('subject_id', $request->subject_id);
            }
            if ($request->filled('chapter_id')) {
                $query->where('chapter_id', $request->chapter_id);
            }
            if ($request->filled('topic_id')) {
                $query->where('topic_id', $request->topic_id);
            }
            if ($request->filled('section_id')) {
                $query->where('section_id', $request->section_id);
            }

            // মাল্টিপল বোর্ড ফিল্টার (JSON Search)
            if ($request->filled('board_id')) {
                $query->whereJsonContains('board_ids', (string)$request->board_id);
            }
            
            // মাল্টিপল ইনস্টিটিউট ফিল্টার (JSON Search)
            if ($request->filled('institute_id')) {
                $query->whereJsonContains('institute_ids', (string)$request->institute_id);
            }

            // সার্চ ফিল্টার
            if ($request->filled('search')) {
                $searchTerm = $request->search;
                $query->where('question', 'LIKE', "%{$searchTerm}%");
            }

            // ৩. পেজিনেশন
            $perPage = $request->input('per_page', 20);
            $mcqs = $query->latest()->paginate($perPage);

            // ৪. ডাটা ট্রান্সফর্ম (নামগুলো মেইন অবজেক্টে নিয়ে আসা)
            $mcqs->getCollection()->transform(function ($item) {
                // Class Names
                $item->class_name_en = $item->class->name_en ?? null;
                $item->class_name_bn = $item->class->name_bn ?? null;

                // Subject Names
                $item->subject_name_en = $item->subject->name_en ?? null;
                $item->subject_name_bn = $item->subject->name_bn ?? null;

                // Chapter Names
                $item->chapter_name_en = $item->chapter->name_en ?? null;
                $item->chapter_name_bn = $item->chapter->name_bn ?? null;

                // Topic Names
                $item->topic_name_en = $item->topic->name_en ?? null;
                $item->topic_name_bn = $item->topic->name_bn ?? null;

                // Section Names
                $item->section_name_en = $item->section->name_en ?? null;
                $item->section_name_bn = $item->section->name_bn ?? null;

                // Department Names
                $item->department_name_en = $item->department->name_en ?? null;
                $item->department_name_bn = $item->department->name_bn ?? null;

                // Category Names
                $item->category_name_en = $item->category->english_name ?? null;
                $item->category_name_bn = $item->category->bangla_name ?? null;

                // মাল্টিপল বোর্ড এবং ইনস্টিটিউটের নামের তালিকা (Bilingual)
                $boards = !empty($item->board_ids) ? Board::whereIn('id', $item->board_ids)->get() : collect();
                $item->board_names_en = $boards->pluck('name_en');
                $item->board_names_bn = $boards->pluck('name_bn');

                $institutes = !empty($item->institute_ids) ? Institute::whereIn('id', $item->institute_ids)->get() : collect();
                $item->institute_names_en = $institutes->pluck('name_en');
                $item->institute_names_bn = $institutes->pluck('name_bn');

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
     */
    public function show(Request $request)
    {
        $id = $request->id; // URL থেকে ID প্যারামিটার নেওয়া হচ্ছে
    
        try {
            $mcq = McqQuestion::with([
                'category', 'class', 'subject', 'chapter', 'topic', 'section', 'department'
            ])->find($id);

            if (!$mcq) {
                return response()->json(['status' => false, 'message' => 'MCQ not found'], 404);
            }

            // নামগুলো মেইন অবজেক্টে নিয়ে আসা (Flattening Data)
            $mcq->class_name_en = $mcq->class->name_en ?? null;
            $mcq->class_name_bn = $mcq->class->name_bn ?? null;

            $mcq->subject_name_en = $mcq->subject->name_en ?? null;
            $mcq->subject_name_bn = $mcq->subject->name_bn ?? null;

            $mcq->chapter_name_en = $mcq->chapter->name_en ?? null;
            $mcq->chapter_name_bn = $mcq->chapter->name_bn ?? null;

            $mcq->section_name_en = $mcq->section->name_en ?? null;
            $mcq->section_name_bn = $mcq->section->name_bn ?? null;

            $mcq->category_name_en = $mcq->category->english_name ?? null;
            $mcq->category_name_bn = $mcq->category->bangla_name ?? null;

            // মাল্টিপল বোর্ড ও ইনস্টিটিউট অবজেক্ট লিস্ট
            $mcq->boards = !empty($mcq->board_ids) ? Board::whereIn('id', $mcq->board_ids)->get() : [];
            $mcq->institutes = !empty($mcq->institute_ids) ? Institute::whereIn('id', $mcq->institute_ids)->get() : [];

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