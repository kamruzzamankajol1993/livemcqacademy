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
            // ১. রিলেশন লোড করা
            $chapters = Chapter::with([
                    'class:id,name_en,name_bn', 
                    'subject:id,name_en,name_bn', 
                    'section:id,name_en,name_bn'
                ])
                ->where('status', 1)
                ->select(
                    'id', 'name_en', 'name_bn', 'slug', 'class_id', 'subject_id', 'section_id', 'serial', 'status'
                )
                ->orderBy('serial', 'asc')
                ->get();

            // ২. ডাটা ট্রান্সফর্ম করে নামগুলো মেইন অবজেক্টে নিয়ে আসা
            $chapters->transform(function ($item) {
                // Class Name
                $item->class_name_en = $item->class->name_en ?? null;
                $item->class_name_bn = $item->class->name_bn ?? null;

                // Subject Name
                $item->subject_name_en = $item->subject->name_en ?? null;
                $item->subject_name_bn = $item->subject->name_bn ?? null;

                // Section Name
                $item->section_name_en = $item->section->name_en ?? null;
                $item->section_name_bn = $item->section->name_bn ?? null;

                // রিলেশন অবজেক্ট হাইড করা
                unset($item->class, $item->subject, $item->section);

                return $item;
            });

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
     * URL: /api/chapters/filter?subject_id=5
     */
    public function filterChapters(Request $request)
    {
        try {
            $query = Chapter::with([
                    'class:id,name_en,name_bn', 
                    'subject:id,name_en,name_bn', 
                    'section:id,name_en,name_bn'
                ])
                ->where('status', 1);

            // ফিল্টার লজিক
            if ($request->has('subject_id') && !empty($request->subject_id)) {
                $query->where('subject_id', $request->subject_id);
            }
            if ($request->has('section_id') && !empty($request->section_id)) {
                $query->where('section_id', $request->section_id);
            }
            if ($request->has('class_id') && !empty($request->class_id)) {
                $query->where('class_id', $request->class_id);
            }

            // ডাটা গেট করা
            $chapters = $query->select(
                    'id', 'name_en', 'name_bn', 'slug', 'class_id', 'subject_id', 'section_id', 'serial', 'status'
                )
                ->orderBy('serial', 'asc')
                ->get();

            // ডাটা ট্রান্সফর্ম
            $chapters->transform(function ($item) {
                $item->class_name_en = $item->class->name_en ?? null;
                $item->class_name_bn = $item->class->name_bn ?? null;

                $item->subject_name_en = $item->subject->name_en ?? null;
                $item->subject_name_bn = $item->subject->name_bn ?? null;

                $item->section_name_en = $item->section->name_en ?? null;
                $item->section_name_bn = $item->section->name_bn ?? null;

                unset($item->class, $item->subject, $item->section);

                return $item;
            });

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