<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ClassDepartment;
use App\Models\SchoolClass;

class DepartmentController extends Controller
{
    // ১. সকল ডিপার্টমেন্ট লিস্ট (All Department List)
    public function index()
    {
        try {
            $departments = ClassDepartment::where('status', 1)
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

            // আইকন/ইমেজ URL প্রসেসিং
            $departments->transform(function ($dept) {
                if (!empty($dept->icon)) {
                    if (!str_contains($dept->icon, 'http')) {
                        $dept->icon = asset('public/'.$dept->icon);
                    }
                } else {
                    $dept->icon = null;
                }
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
            // SchoolClass মডেলে departments() রিলেশনশিপ (belongsToMany) ব্যবহার করা হয়েছে
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

            // আইকন/ইমেজ URL প্রসেসিং
            $departments->transform(function ($dept) {
                if (!empty($dept->icon)) {
                    if (!str_contains($dept->icon, 'http')) {
                        $dept->icon = asset('public/'.$dept->icon);
                    }
                } else {
                    $dept->icon = null;
                }
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