<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Feature;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;

class FeatureController extends Controller
{
    // Reorder Method (New)
public function reorder(Request $request)
{
    $features = Feature::all();
    foreach ($features as $feature) {
        foreach ($request->order as $order) {
            if ($order['id'] == $feature->id) {
                $feature->update(['serial' => $order['position']]);
            }
        }
    }
    return response()->json(['status' => 'success']);
}

// Index Method Update (যাতে লিস্ট সিরিয়াল অনুযায়ী আসে)
public function index()
{
    // সিরিয়াল অনুযায়ী সর্ট করে ডাটা পাঠাতে হবে
    $allFeatures = Feature::orderBy('serial', 'asc')->get(); 
    return view('admin.feature.index', compact('allFeatures'));
}

    // Ajax Table Data
    public function data(Request $request)
    {
        $query = Feature::with('parent');

        if ($request->filled('search')) {
            $query->where('english_name', 'like', $request->search . '%')
                  ->orWhere('bangla_name', 'like', $request->search . '%');
        }

        $sort = $request->get('sort', 'id');
        $direction = $request->get('direction', 'desc');
        
        $query->orderBy($sort, $direction);

        $features = $query->paginate(10);

        return response()->json([
            'data' => $features->items(),
            'total' => $features->total(),
            'current_page' => $features->currentPage(),
            'last_page' => $features->lastPage(),
            'per_page' => $features->perPage(),
        ]);
    }

    // Fetch Single Data for Edit Modal
    public function show($id)
    {
        $feature = Feature::findOrFail($id);
        return response()->json($feature);
    }

    public function store(Request $request)
    {
        $request->validate([
            'bangla_name' => 'required|string',
            'english_name' => 'required|string|unique:features,english_name',
            'image' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
            'color' => 'required',
            'status' => 'required'
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = 'feat_'.time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/features');

            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }

            Image::read($image->getRealPath())->resize(80, 80)->save($destinationPath.'/'.$imageName);
            $path = 'uploads/features/'.$imageName;
        }

        Feature::create([
            'parent_id' => $request->parent_id,
            'bangla_name' => $request->bangla_name,
            'english_name' => $request->english_name,
            'slug' => Str::slug($request->english_name),
            'color' => $request->color,
            'short_description' => $request->short_description,
            'status' => $request->status,
            'image' => $path,
        ]);

        return redirect()->back()->with('success', 'Feature created successfully!');
    }

    public function update(Request $request, $id)
    {
        $feature = Feature::findOrFail($id);

        $request->validate([
            'bangla_name' => 'required|string',
            'english_name' => 'required|string|unique:features,english_name,' . $feature->id,
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $path = $feature->image;

        if ($request->hasFile('image')) {
            if ($feature->image && File::exists(public_path($feature->image))) {
                File::delete(public_path($feature->image));
            }

            $image = $request->file('image');
            $imageName = 'feat_'.time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/features');

            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0777, true, true);
            }

            Image::read($image->getRealPath())->resize(80, 80)->save($destinationPath.'/'.$imageName);
            $path = 'uploads/features/'.$imageName;
        }

        $feature->update([
            'parent_id' => $request->parent_id,
            'bangla_name' => $request->bangla_name,
            'english_name' => $request->english_name,
            'slug' => Str::slug($request->english_name),
            'color' => $request->color,
            'short_description' => $request->short_description,
            'status' => $request->status,
            'image' => $path,
        ]);

        return redirect()->back()->with('success', 'Feature updated successfully!');
    }

    public function destroy($id)
    {
        $feature = Feature::findOrFail($id);
        if ($feature->image && File::exists(public_path($feature->image))) {
            File::delete(public_path($feature->image));
        }
        $feature->delete();
        return redirect()->back()->with('success', 'Feature deleted successfully!');
    }
}