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
            // ১. রিলেশন লোড করা
            // Category টেবিলে english_name/bangla_name ফিল্ড থাকে
            $sections = Section::with([
                    'category:id,english_name,bangla_name', 
                    'class:id,name_en,name_bn', 
                    'subject:id,name_en,name_bn'
                ])
                ->where('status', 1)
                ->select(
                    'id', 'name_en', 'name_bn', 'slug', 'category_id', 'class_id', 'subject_id', 'serial', 'status'
                )
                ->orderBy('serial', 'asc')
                ->get();

            // ২. ডাটা ট্রান্সফর্ম
            $sections->transform(function ($item) {
                // Category Name
                if ($item->category) {
                    $item->category_name_en = $item->category->english_name;
                    $item->category_name_bn = $item->category->bangla_name;
                } else {
                    $item->category_name_en = null;
                    $item->category_name_bn = null;
                }

                // Class Name
                $item->class_name_en = $item->class->name_en ?? null;
                $item->class_name_bn = $item->class->name_bn ?? null;

                // Subject Name
                $item->subject_name_en = $item->subject->name_en ?? null;
                $item->subject_name_bn = $item->subject->name_bn ?? null;

                // রিলেশন অবজেক্ট হাইড করা
                unset($item->category, $item->class, $item->subject);

                return $item;
            });

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
     * URL: /api/sections/filter?class_id=1
     */
    public function filterSections(Request $request)
    {
        try {
            $query = Section::with([
                    'category:id,english_name,bangla_name', 
                    'class:id,name_en,name_bn', 
                    'subject:id,name_en,name_bn'
                ])
                ->where('status', 1);

            // ফিল্টার লজিক
            if ($request->has('category_id') && !empty($request->category_id)) {
                $query->where('category_id', $request->category_id);
            }
            if ($request->has('class_id') && !empty($request->class_id)) {
                $query->where('class_id', $request->class_id);
            }
            if ($request->has('subject_id') && !empty($request->subject_id)) {
                $query->where('subject_id', $request->subject_id);
            }

            // ডাটা গেট করা
            $sections = $query->select(
                    'id', 'name_en', 'name_bn', 'slug', 'category_id', 'class_id', 'subject_id', 'serial', 'status'
                )
                ->orderBy('serial', 'asc')
                ->get();

            // ডাটা ট্রান্সফর্ম
            $sections->transform(function ($item) {
                if ($item->category) {
                    $item->category_name_en = $item->category->english_name;
                    $item->category_name_bn = $item->category->bangla_name;
                } else {
                    $item->category_name_en = null;
                    $item->category_name_bn = null;
                }

                $item->class_name_en = $item->class->name_en ?? null;
                $item->class_name_bn = $item->class->name_bn ?? null;

                $item->subject_name_en = $item->subject->name_en ?? null;
                $item->subject_name_bn = $item->subject->name_bn ?? null;

                unset($item->category, $item->class, $item->subject);

                return $item;
            });

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