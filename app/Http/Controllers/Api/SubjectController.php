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
 * ২. ইউজারের প্রোফাইল অনুযায়ী সাবজেক্ট ফিল্টার (Automatic Filter)
 * URL: /api/subjects_filter
 */
public function filterSubjects(Request $request)
{
    try {
        $user = $request->user();

        // ১. চেক করা ইউজারের ক্লাস আইডি আছে কি না
        if (empty($user->class_id)) {
            return response()->json([
                'status' => false,
                'message' => 'Please update your academic information (Class) first.',
                'update_required' => true
            ], 403); // Forbidden access until profile update
        }

        $query = Subject::with([
                'classes:id,name_en,name_bn', 
                'departments:id,name_en,name_bn'
            ])
            ->where('status', 1);

        // ২. ইউজারের class_id অনুযায়ী ফিল্টার
        $query->whereHas('classes', function ($q) use ($user) {
            $q->where('school_classes.id', $user->class_id);
        });

        // ৩. ইউজারের department_id থাকলে সেটি দিয়েও ফিল্টার হবে (যদি না থাকে তবে শুধু ক্লাসের সাবজেক্ট আসবে)
        if (!empty($user->department_id)) {
            $query->whereHas('departments', function ($q) use ($user) {
                $q->where('class_departments.id', $user->department_id);
            });
        }

        // ৪. ডাটা সিলেকশন ও পেজিনেশন
        $subjects = $query->select('id', 'name_en', 'name_bn', 'slug', 'color', 'icon', 'serial', 'status')
            ->orderBy('serial', 'asc')
            ->simplePaginate(20); 

        return $this->formatResponse($subjects, 'Subjects retrieved based on your profile.');

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