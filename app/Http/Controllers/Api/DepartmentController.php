<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassDepartment;
use App\Models\SchoolClass;

class DepartmentController extends Controller
{
    // ১. সকল ডিপার্টমেন্ট লিস্ট (সাথে ক্লাসের তথ্য)
    public function index()
    {
        try {
            // with('classes') দিয়ে রিলেশন লোড করা হলো
            $departments = ClassDepartment::with('classes:id,name_en,name_bn')
                ->where('status', 1)
                ->select(
                    'id',
                    'name_en',
                    'name_bn',
                    'slug',
                    'color',
                    'icon',
                    'serial',
                    'status'
                )
                ->orderBy('serial', 'asc')
                ->get();

            // ডাটা প্রসেসিং
            $departments->transform(function ($dept) {
                // ১. আইকন/ইমেজ URL প্রসেসিং
                if (!empty($dept->icon)) {
                    if (!str_contains($dept->icon, 'http')) {
                        $dept->icon = asset('public/' . $dept->icon);
                    }
                } else {
                    $dept->icon = null;
                }

                // ২. ক্লাসের নামগুলো সুন্দরভাবে ফরম্যাট করা (Optional)
                // আপনি চাইলে সরাসরি $dept->classes ব্যবহার করতে পারেন, অথবা কাস্টম ফিল্ড বানাতে পারেন
                $dept->class_list = $dept->classes->map(function ($cls) {
                    return [
                        'id' => $cls->id,
                        'name_en' => $cls->name_en,
                        'name_bn' => $cls->name_bn,
                    ];
                });

                // মেইন classes রিলেশন হাইড করা (রেসপন্স ক্লিন রাখার জন্য)
                unset($dept->classes);

                return $dept;
            });

            return response()->json([
                'status' => true,
                'message' => 'Department list retrieved successfully',
                'data' => $departments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // ২. ক্লাস অনুযায়ী ডিপার্টমেন্ট লিস্ট (Class Wise Department List - By ID)
    public function getDepartmentsByClass($classId)
    {
        try {
            // ক্লাস চেক করা
            $class = SchoolClass::where('id', $classId)->where('status', 1)->first();

            if (!$class) {
                return response()->json([
                    'status' => false,
                    'message' => 'Class not found'
                ], 404);
            }

            // ওই ক্লাসের সাথে অ্যাসাইন করা ডিপার্টমেন্টগুলো আনা
            $departments = $class->departments()
                ->where('class_departments.status', 1)
                ->select(
                    'class_departments.id',
                    'class_departments.name_en',
                    'class_departments.name_bn',
                    'class_departments.slug',
                    'class_departments.color',
                    'class_departments.icon',
                    'class_departments.serial',
                    'class_departments.status'
                )
                ->orderBy('class_departments.serial', 'asc')
                ->get();

            // ডাটা প্রসেসিং
            $departments->transform(function ($dept) use ($class) {
                // ১. আইকন URL প্রসেসিং
                if (!empty($dept->icon)) {
                    if (!str_contains($dept->icon, 'http')) {
                        $dept->icon = asset('public/' . $dept->icon);
                    }
                } else {
                    $dept->icon = null;
                }

                // ২. ক্লাস নেম সংযুক্ত করা (যেহেতু আমরা নির্দিষ্ট ক্লাসের সাপেক্ষে ডাটা আনছি)
                $dept->class_name_en = $class->name_en;
                $dept->class_name_bn = $class->name_bn;

                return $dept;
            });

            return response()->json([
                'status' => true,
                'message' => 'Departments retrieved for class ID: ' . $class->id,
                'data' => $departments
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}