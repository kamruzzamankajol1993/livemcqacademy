<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Feature; // Feature মডেল ইম্পোর্ট করুন
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;

class CategoryController extends Controller
{
    public function index()
    {
        // ড্রপডাউনের জন্য সব ক্যাটাগরি ও ফিচার পাঠানো হচ্ছে
        $categories = Category::orderBy('serial', 'asc')->get(); // ড্র্যাগ এন্ড ড্রপ এর জন্য serial অনুযায়ী অর্ডার
        $features = Feature::where('status', 1)->get(); // ফিচার লিস্ট
        
        return view('admin.category.index', compact('categories', 'features'));
    }

    public function data(Request $request)
    {
        $query = Category::with(['parent', 'feature']); // Feature রিলেশন লোড করা হলো

        if ($request->filled('search')) {
            $query->where('english_name', 'like', $request->search . '%')
                  ->orWhere('bangla_name', 'like', $request->search . '%');
        }

        // ডিফল্ট সর্টিং সিরিয়াল অনুযায়ী হবে
        $sort = $request->get('sort', 'serial'); 
        $direction = $request->get('direction', 'asc');
        $query->orderBy($sort, $direction);

        $categories = $query->paginate(10);

        return response()->json([
            'data' => $categories->items(),
            'total' => $categories->total(),
            'current_page' => $categories->currentPage(),
            'last_page' => $categories->lastPage(),
            'per_page' => $categories->perPage(),
        ]);
    }
    
    // ড্র্যাগ এন্ড ড্রপ এর জন্য নতুন ফাংশন
    public function reorder(Request $request)
    {
        foreach ($request->order as $order) {
            Category::where('id', $order['id'])->update(['serial' => $order['position']]);
        }
        return response()->json(['status' => 'success']);
    }

    public function show($id)
    {
        $category = Category::findOrFail($id);
        return response()->json($category);
    }

    public function store(Request $request)
    {
        $request->validate([
            'english_name' => 'required|string',
            'bangla_name' => 'required|string',
            'feature_id' => 'nullable|exists:features,id',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = 'cat_'.time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/categories');
            if (!File::isDirectory($destinationPath)) File::makeDirectory($destinationPath, 0777, true, true);
            Image::read($image->getRealPath())->resize(50, 50)->save($destinationPath.'/'.$imageName);
            $path = 'uploads/categories/'.$imageName;
        }

        Category::create([
            'feature_id' => $request->feature_id,
            'name' => $request->english_name, // name কলামে ইংলিশ নাম রাখা হচ্ছে ব্যাকওয়ার্ড কম্প্যাটিবিলিটির জন্য
            'english_name' => $request->english_name,
            'bangla_name' => $request->bangla_name,
            'slug' => Str::slug($request->english_name),
            'parent_id' => $request->parent_id,
            'color' => $request->color,
            'image' => $path,
            'status' => 1,
        ]);

        return redirect()->back()->with('success', 'Category created successfully!');
    }

    public function update(Request $request, $id)
    {
        $category = Category::findOrFail($id);
        
        $request->validate([
            'english_name' => 'required|string',
            'bangla_name' => 'required|string',
        ]);

        $path = $category->image;
        if ($request->hasFile('image')) {
            if ($category->image && File::exists(public_path($category->image))) File::delete(public_path($category->image));
            $image = $request->file('image');
            $imageName = 'cat_'.time().'.'.$image->getClientOriginalExtension();
            $destinationPath = public_path('uploads/categories');
            if (!File::isDirectory($destinationPath)) File::makeDirectory($destinationPath, 0777, true, true);
            Image::read($image->getRealPath())->resize(50, 50)->save($destinationPath.'/'.$imageName);
            $path = 'uploads/categories/'.$imageName;
        }

        $category->update([
            'feature_id' => $request->feature_id,
            'name' => $request->english_name,
            'english_name' => $request->english_name,
            'bangla_name' => $request->bangla_name,
            'parent_id' => $request->parent_id,
            'color' => $request->color,
            'status' => $request->status,
            'image' => $path,
        ]);

        return redirect()->back()->with('success', 'Category updated successfully!');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        if ($category->image && File::exists(public_path($category->image))) File::delete(public_path($category->image));
        $category->delete();
        return redirect()->route('category.index')->with('success', 'Category deleted successfully!');
    }
}