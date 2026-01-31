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
            // ১. রিলেশন লোড করা (Eager Loading)
            $topics = Topic::with([
                    'class:id,name_en,name_bn', 
                    'subject:id,name_en,name_bn', 
                    'chapter:id,name_en,name_bn'
                ])
                ->where('status', 1)
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

            // ২. ডাটা ট্রান্সফর্ম করে নামগুলো মেইন অবজেক্টে নিয়ে আসা
            $topics->transform(function ($item) {
                // Class Name
                $item->class_name_en = $item->class->name_en ?? null;
                $item->class_name_bn = $item->class->name_bn ?? null;

                // Subject Name
                $item->subject_name_en = $item->subject->name_en ?? null;
                $item->subject_name_bn = $item->subject->name_bn ?? null;

                // Chapter Name
                $item->chapter_name_en = $item->chapter->name_en ?? null;
                $item->chapter_name_bn = $item->chapter->name_bn ?? null;

                // রিলেশন অবজেক্ট হাইড করা (রেসপন্স ক্লিন রাখার জন্য)
                unset($item->class, $item->subject, $item->chapter);

                return $item;
            });

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
     * URL: /api/topics/filter?chapter_id=10
     */
    public function filterTopics(Request $request)
    {
        try {
            $query = Topic::with([
                    'class:id,name_en,name_bn', 
                    'subject:id,name_en,name_bn', 
                    'chapter:id,name_en,name_bn'
                ])
                ->where('status', 1);

            // ১. চ্যাপ্টার ফিল্টার
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

            // ডাটা গেট করা
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

            // ডাটা ট্রান্সফর্ম
            $topics->transform(function ($item) {
                // Class Name
                $item->class_name_en = $item->class->name_en ?? null;
                $item->class_name_bn = $item->class->name_bn ?? null;

                // Subject Name
                $item->subject_name_en = $item->subject->name_en ?? null;
                $item->subject_name_bn = $item->subject->name_bn ?? null;

                // Chapter Name
                $item->chapter_name_en = $item->chapter->name_en ?? null;
                $item->chapter_name_bn = $item->chapter->name_bn ?? null;

                // রিলেশন হাইড
                unset($item->class, $item->subject, $item->chapter);

                return $item;
            });

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