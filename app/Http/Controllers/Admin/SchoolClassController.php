<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\SchoolClassImport;
use App\Exports\SampleClassExport;
class SchoolClassController extends Controller
{

public function downloadSample()
    {
        return Excel::download(new SampleClassExport, 'class_import_sample.xlsx');
    }
    public function index()
    {
        // ড্র্যাগ এন্ড ড্রপ অর্ডারে ডাটা পাঠানো হচ্ছে
        $classes = SchoolClass::with('categories')->orderBy('serial', 'asc')->get();
        $categories = Category::where('status', 1)->get(); // মডালের জন্য
        return view('admin.class.index', compact('classes', 'categories'));
    }

    public function data(Request $request)
    {
        $query = SchoolClass::with('categories');

        if ($request->filled('search')) {
            $query->where('name_en', 'like', $request->search . '%')
                  ->orWhere('name_bn', 'like', $request->search . '%');
        }

        $sort = $request->get('sort', 'serial');
        $direction = $request->get('direction', 'asc');
        $query->orderBy($sort, $direction);

        $data = $query->paginate(10);

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

    public function reorder(Request $request)
    {
        foreach ($request->order as $order) {
            SchoolClass::where('id', $order['id'])->update(['serial' => $order['position']]);
        }
        return response()->json(['status' => 'success']);
    }

    public function show($id)
    {
        // ক্যাটাগরি সহ ডাটা রিটার্ন করবে
        $class = SchoolClass::with('categories')->findOrFail($id);
        return response()->json($class);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string',
            'name_bn' => 'required|string',
            'category_ids' => 'required|array', // মাল্টিপল ক্যাটাগরি
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = 'cls_'.time().'.'.$image->getClientOriginalExtension();
            $path = 'uploads/classes/';
            if (!File::isDirectory(public_path($path))) File::makeDirectory(public_path($path), 0777, true, true);
            Image::read($image->getRealPath())->resize(80, 80)->save(public_path($path.$imageName));
            $path = $path.$imageName;
        }

        $class = SchoolClass::create([
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'color' => $request->color,
            'image' => $path,
            'status' => $request->status ?? 1,
            'serial' => SchoolClass::max('serial') + 1,
        ]);

        // পিভট টেবিলে ডাটা সেভ
        $class->categories()->sync($request->category_ids);

        return redirect()->back()->with('success', 'Class created successfully!');
    }

    public function update(Request $request, $id)
    {
        $class = SchoolClass::findOrFail($id);
        
        $request->validate([
            'name_en' => 'required|string',
            'name_bn' => 'required|string',
            'category_ids' => 'required|array',
        ]);

        $path = $class->image;
        if ($request->hasFile('image')) {
            if ($class->image && File::exists(public_path($class->image))) File::delete(public_path($class->image));
            $image = $request->file('image');
            $imageName = 'cls_'.time().'.'.$image->getClientOriginalExtension();
            $folder = 'uploads/classes/';
            if (!File::isDirectory(public_path($folder))) File::makeDirectory(public_path($folder), 0777, true, true);
            Image::read($image->getRealPath())->resize(80, 80)->save(public_path($folder.$imageName));
            $path = $folder.$imageName;
        }

        $class->update([
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'color' => $request->color,
            'status' => $request->status,
            'image' => $path,
        ]);

        // ক্যাটাগরি আপডেট (Sync)
        $class->categories()->sync($request->category_ids);

        return redirect()->back()->with('success', 'Class updated successfully!');
    }

    public function destroy($id)
    {
        $class = SchoolClass::findOrFail($id);
        if ($class->image && File::exists(public_path($class->image))) File::delete(public_path($class->image));
        $class->delete(); // Cascade delete এর কারণে assign টেবিলের ডাটাও ডিলিট হবে (যদি মাইগ্রেশনে সেট করা থাকে)
        return redirect()->back()->with('success', 'Class deleted successfully!');
    }

    // Excel Import Method
    public function import(Request $request) 
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv',
        ]);
        
        Excel::import(new SchoolClassImport, $request->file('file'));
        
        return redirect()->back()->with('success', 'Classes imported successfully!');
    }
}