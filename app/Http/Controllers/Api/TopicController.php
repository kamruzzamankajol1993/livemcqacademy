<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Topic;

class TopicController extends Controller
{
    /**
     * ১. সকল টপিক লিস্ট (All Topic List)
     * URL: /api/topics
     */
    public function index()
    {
        try {
            $topics = Topic::where('status', 1)
                ->select(
                    'id', 
                    'name_en', 
                    'name_bn', 
                    'slug', 
                    'class_id', 
                    'subject_id', 
                    'chapter_id', 
                    'serial', 
                    'status'
                )
                ->orderBy('serial', 'asc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'All topics retrieved successfully',
                'data' => $topics
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ২. ফিল্টার টপিক (Filter Topics)
     * এই API টি chapter_id (Primary) অথবা subject_id/class_id দিয়ে ফিল্টার করবে।
     * * URL Examples:
     * - Chapter Wise: /api/topics/filter?chapter_id=10
     * - Subject Wise: /api/topics/filter?subject_id=5
     * - Combined:     /api/topics/filter?chapter_id=10&subject_id=5
     */
    public function filterTopics(Request $request)
    {
        try {
            $query = Topic::where('status', 1);

            // ১. চ্যাপ্টার ফিল্টার (Primary Filter - সাধারণত টপিক চ্যাপ্টারের আন্ডারেই লোড হয়)
            if ($request->has('chapter_id') && !empty($request->chapter_id)) {
                $query->where('chapter_id', $request->chapter_id);
            }

            // ২. সাবজেক্ট ফিল্টার
            if ($request->has('subject_id') && !empty($request->subject_id)) {
                $query->where('subject_id', $request->subject_id);
            }

            // ৩. ক্লাস ফিল্টার
            if ($request->has('class_id') && !empty($request->class_id)) {
                $query->where('class_id', $request->class_id);
            }

            // ডাটা সিলেকশন ও অর্ডারিং
            $topics = $query->select(
                    'id', 
                    'name_en', 
                    'name_bn', 
                    'slug', 
                    'class_id', 
                    'subject_id', 
                    'chapter_id', 
                    'serial', 
                    'status'
                )
                ->orderBy('serial', 'asc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Topics retrieved successfully based on filters.',
                'data' => $topics
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}