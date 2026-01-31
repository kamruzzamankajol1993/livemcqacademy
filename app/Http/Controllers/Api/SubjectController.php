<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subject;

class SubjectController extends Controller
{
    /**
     * ১. সকল সাবজেক্ট লিস্ট (All Subject List)
     * URL: /api/subjects
     */
    public function index()
    {
        try {
            // Relation Load (classes & departments)
            $subjects = Subject::with([
                    'classes:id,name_en,name_bn', 
                    'departments:id,name_en,name_bn'
                ])
                ->where('status', 1)
                ->select('id', 'name_en', 'name_bn', 'slug', 'color', 'icon', 'serial', 'status')
                ->orderBy('serial', 'asc')
                ->simplePaginate(20); 

            return $this->formatResponse($subjects, 'All subject list retrieved successfully');

        } catch (\Exception $e) {
            return $this->errorResponse($e);
        }
    }

    /**
     * ২. ফিল্টার সাবজেক্ট (Filter Subjects)
     * URL: /api/subjects/filter?class_id=1&department_id=2
     */
    public function filterSubjects(Request $request)
    {
        try {
            $query = Subject::with([
                    'classes:id,name_en,name_bn', 
                    'departments:id,name_en,name_bn'
                ])
                ->where('status', 1);

            // ক্লাস ফিল্টার
            if ($request->has('class_id') && !empty($request->class_id)) {
                $query->whereHas('classes', function ($q) use ($request) {
                    $q->where('school_classes.id', $request->class_id);
                });
            }

            // ডিপার্টমেন্ট ফিল্টার
            if ($request->has('department_id') && !empty($request->department_id)) {
                $query->whereHas('departments', function ($q) use ($request) {
                    $q->where('class_departments.id', $request->department_id);
                });
            }

            // ডাটা সিলেকশন ও পেজিনেশন
            $subjects = $query->select('id', 'name_en', 'name_bn', 'slug', 'color', 'icon', 'serial', 'status')
                ->orderBy('serial', 'asc')
                ->simplePaginate(20); 

            return $this->formatResponse($subjects, 'Subjects retrieved successfully based on filters.');

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper Method: রেসপন্স ফরম্যাট, ইমেজ URL এবং রিলেশন প্রসেসিং
     */
    private function formatResponse($data, $message)
    {
        // getCollection() ব্যবহার করা হয়েছে কারণ $data একটি Paginator অবজেক্ট
        $data->getCollection()->transform(function ($item) {
            
            // ১. ইমেজ URL প্রসেসিং
            if (!empty($item->icon)) {
                if (!str_contains($item->icon, 'http')) {
                    $item->icon = asset('public/' . $item->icon);
                }
            } else {
                $item->icon = null;
            }

            // ২. ক্লাস লিস্ট তৈরি (Many-to-Many Relation)
            // সাবজেক্টটি কোন কোন ক্লাসের সাথে যুক্ত তার লিস্ট
            $item->class_list = $item->classes->map(function($cls) {
                return [
                    'id' => $cls->id,
                    'name_en' => $cls->name_en,
                    'name_bn' => $cls->name_bn,
                ];
            });

            // ৩. ডিপার্টমেন্ট লিস্ট তৈরি (Many-to-Many Relation)
            // সাবজেক্টটি কোন কোন ডিপার্টমেন্টের সাথে যুক্ত তার লিস্ট
            $item->department_list = $item->departments->map(function($dept) {
                return [
                    'id' => $dept->id,
                    'name_en' => $dept->name_en,
                    'name_bn' => $dept->name_bn,
                ];
            });

            // মেইন রিলেশন অবজেক্ট হাইড করা (রেসপন্স ক্লিন রাখার জন্য)
            unset($item->classes, $item->departments);

            return $item;
        });

        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], 200);
    }

    /**
     * Helper Method: এরর রেসপন্স
     */
    private function errorResponse($e)
    {
        return response()->json([
            'status' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
}