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
            $subjects = Subject::where('status', 1)
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
     * এই একটি মেথড ৩টি কাজ করবে:
     * - শুধু ক্লাস দিয়ে ফিল্টার (class_id)
     * - শুধু ডিপার্টমেন্ট দিয়ে ফিল্টার (department_id)
     * - ক্লাস এবং ডিপার্টমেন্ট উভয় দিয়ে ফিল্টার (class_id & department_id)
     * * URL: /api/subjects/filter?class_id=1&department_id=2
     */
    public function filterSubjects(Request $request)
    {
        try {
            $query = Subject::where('status', 1);

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

            // ডাটা সিলেকশন ও পেজিনেশন (২০টি করে ডাটা দিবে)
            // অ্যাপের জন্য simplePaginate() ভালো, কারণ এতে next_page_url থাকে
            $subjects = $query->select('id', 'name_en', 'name_bn', 'slug', 'color', 'icon', 'serial', 'status')
                ->orderBy('serial', 'asc')
                ->simplePaginate(20); 

            // ইমেজ URL প্রসেসিং (পেজিনেটেড ডাটার ওপর লুপ চালানো)
            $subjects->getCollection()->transform(function ($item) {
                if (!empty($item->icon)) {
                    if (!str_contains($item->icon, 'http')) {
                        $item->icon = asset('public/'.$item->icon);
                    }
                } else {
                    $item->icon = null;
                }
                return $item;
            });

            return response()->json([
                'status' => true,
                'message' => 'Subjects retrieved successfully.',
                'data' => $subjects
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper Method: রেসপন্স ফরম্যাট এবং ইমেজ URL প্রসেসিং
     */
    private function formatResponse($data, $message)
    {
        // ইমেজ URL প্রসেসিং
        $data->transform(function ($item) {
            if (!empty($item->icon)) {
                // যদি ইমেজে http না থাকে (অর্থাৎ লোকাল পাথ), তাহলে asset() দিয়ে ফুল লিংক বানানো
                if (!str_contains($item->icon, 'http')) {
                    $item->icon = asset('public/'.$item->icon);
                }
            } else {
                $item->icon = null;
            }
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