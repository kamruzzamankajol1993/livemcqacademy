<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FeatureList;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FeatureListController extends Controller
{
    public function index()
    {
        return view('admin.feature_list.index');
    }

    // AJAX Data for Table
    public function data(Request $request)
    {
        $query = FeatureList::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('code', 'like', '%' . $request->search . '%');
        }

        $data = $query->orderBy('id', 'desc')->paginate(10);

        return response()->json([
            'data' => $data->items(),
            'total' => $data->total(),
            'current_page' => $data->currentPage(),
            'last_page' => $data->lastPage(),
            'per_page' => $data->perPage(),
            'from' => $data->firstItem(),
            'to' => $data->lastItem(),
        ]);
    }

    public function create()
    {
        return view('admin.feature_list.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // অটো ইউনিক কোড জেনারেশন
        $code = Str::slug($request->name, '_');
        $originalCode = $code;
        $counter = 1;

        while (FeatureList::where('code', $code)->exists()) {
            $code = $originalCode . '_' . Str::lower(Str::random(4));
        }

        FeatureList::create([
            'name' => $request->name,
            'code' => $code,
            'status' => $request->status ?? 1
        ]);

        return redirect()->route('feature-list.index')->with('success', 'Feature created successfully with code: ' . $code);
    }

    public function edit($id)
    {
        $feature = FeatureList::findOrFail($id);
        return view('admin.feature_list.edit', compact('feature'));
    }

    public function update(Request $request, $id)
    {
        $feature = FeatureList::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        // নাম পরিবর্তন হলে কোডও আপডেট হবে, অন্যথায় আগেরটাই থাকবে
        $code = $feature->code;
        if($feature->name !== $request->name) {
            $code = Str::slug($request->name, '_');
            if (FeatureList::where('code', $code)->where('id', '!=', $id)->exists()) {
                $code = $code . '_' . Str::lower(Str::random(4));
            }
        }

        $feature->update([
            'name' => $request->name,
            'code' => $code,
            'status' => $request->status
        ]);

        return redirect()->route('feature-list.index')->with('success', 'Feature updated successfully!');
    }

    public function destroy($id)
    {
        FeatureList::findOrFail($id)->delete();
        return redirect()->route('feature-list.index')->with('success', 'Feature deleted successfully!');
    }
}