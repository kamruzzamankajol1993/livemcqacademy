<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Institute;

class InstituteController extends Controller
{
    /**
     * ১. সকল ইনস্টিটিউট লিস্ট (All List)
     * URL: /api/institutes
     */
    public function index()
    {
        try {
            $institutes = Institute::where('status', 1)
                ->select('id', 'name_en', 'name_bn', 'slug', 'type', 'serial', 'status')
                ->orderBy('serial', 'asc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'All institutes retrieved successfully',
                'data' => $institutes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ২. টাইপ অনুযায়ী ইনস্টিটিউট লিস্ট (Type Wise List)
     * URL Example: /api/institutes/type/school
     * URL Example: /api/institutes/type/college
     */
    public function getByType(Request $request)
    {
        $type = $request->type;
        try {
            // টাইপ অনুযায়ী ফিল্টার
            $institutes = Institute::where('status', 1)
                ->where('type', $type)
                ->select('id', 'name_en', 'name_bn', 'slug', 'type', 'serial', 'status')
                ->orderBy('serial', 'asc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => "Institutes of type '{$type}' retrieved successfully",
                'data' => $institutes
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}