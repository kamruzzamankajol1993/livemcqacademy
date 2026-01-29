<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Board;

class BoardController extends Controller
{
    /**
     * সকল বোর্ড লিস্ট (Board List)
     * URL: /api/boards
     */
    public function index()
    {
        try {
            $boards = Board::where('status', 1)
                ->select('id', 'name_en', 'name_bn', 'slug', 'serial', 'status')
                ->orderBy('serial', 'asc')
                ->get();

            return response()->json([
                'status' => true,
                'message' => 'Boards retrieved successfully',
                'data' => $boards
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}