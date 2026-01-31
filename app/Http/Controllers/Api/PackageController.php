<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Exception;

class PackageController extends Controller
{
    /**
     * সকল একটিভ প্যাকেজ এবং তাদের ফিচারের লিস্ট
     */
    public function index()
    {
        try {
            // শুধুমাত্র একটিভ প্যাকেজগুলো এবং তাদের ফিচারগুলো রিলেশনসহ নিয়ে আসা
            $packages = Package::with(['features' => function($query) {
                $query->where('status', 1); // শুধুমাত্র একটিভ ফিচারগুলো দেখাবে
            }])
            ->where('status', 1)
            ->orderBy('id', 'desc')
            ->get();

            return response()->json([
                'status'  => true,
                'message' => 'Active packages list with features',
                'data'    => $packages
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Failed to fetch packages: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * নির্দিষ্ট একটি প্যাকেজের ডিটেইলস
     */
    public function show($id)
    {
        try {
            $package = Package::with('features')->where('status', 1)->findOrFail($id);

            return response()->json([
                'status'  => true,
                'message' => 'Package details with features',
                'data'    => $package
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'status'  => false,
                'message' => 'Package not found or inactive.'
            ], 404);
        }
    }
}