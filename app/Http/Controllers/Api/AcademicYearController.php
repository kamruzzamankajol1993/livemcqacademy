<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicYear;

class AcademicYearController extends Controller
{
    /**
     * সকল শিক্ষাবর্ষ লিস্ট (Academic Year List)
     * URL: /api/academic-years
     */
    public function index()
    {
        try {
            $years = AcademicYear::where('status', 1)
                ->select('id', 'name_en', 'name_bn', 'slug', 'serial', 'status')
                ->orderBy('serial', 'asc') // বা 'name_en' desc যদি লেটেস্ট ইয়ার আগে চান
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Academic years retrieved successfully',
                'data' => $years
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}