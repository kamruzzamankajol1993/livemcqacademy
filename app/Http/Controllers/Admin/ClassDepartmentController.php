<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClassDepartment;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image; // আপনার প্রজেক্টের ইমেজ প্যাকেজ অনুযায়ী

class ClassDepartmentController extends Controller
{
    public function index()
    {
        $departments = ClassDepartment::with('classes')->orderBy('serial', 'asc')->get();
        $classes = SchoolClass::where('status', 1)->get(); // For Dropdown
        return view('admin.class_department.index', compact('departments', 'classes'));
    }

    public function data(Request $request)
    {
        $query = ClassDepartment::with('classes');

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

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string',
            'name_bn' => 'required|string',
            'class_ids' => 'required|array', // Multiple Classes
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('icon')) {
            $image = $request->file('icon');
            $imageName = 'dept_'.time().'.'.$image->getClientOriginalExtension();
            $folder = 'uploads/class_departments/';
            if (!File::isDirectory(public_path($folder))) File::makeDirectory(public_path($folder), 0777, true, true);
            Image::read($image->getRealPath())->resize(80, 80)->save(public_path($folder.$imageName));
            $path = $folder.$imageName;
        }

        $department = ClassDepartment::create([
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'color' => $request->color,
            'icon' => $path,
            'status' => $request->status ?? 1,
            'serial' => (ClassDepartment::max('serial') ?? 0) + 1,
        ]);

        // Sync Classes
        $department->classes()->sync($request->class_ids);

        return redirect()->back()->with('success', 'Department created successfully!');
    }

    public function update(Request $request, $id)
    {
        $department = ClassDepartment::findOrFail($id);
        $request->validate([
            'name_en' => 'required|string',
            'name_bn' => 'required|string',
            'class_ids' => 'required|array',
        ]);

        $path = $department->icon;
        if ($request->hasFile('icon')) {
            if ($department->icon && File::exists(public_path($department->icon))) File::delete(public_path($department->icon));
            
            $image = $request->file('icon');
            $imageName = 'dept_'.time().'.'.$image->getClientOriginalExtension();
            $folder = 'uploads/class_departments/';
            if (!File::isDirectory(public_path($folder))) File::makeDirectory(public_path($folder), 0777, true, true);
            Image::read($image->getRealPath())->resize(80, 80)->save(public_path($folder.$imageName));
            $path = $folder.$imageName;
        }

        $department->update([
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'color' => $request->color,
            'icon' => $path,
            'status' => $request->status,
        ]);

        // Sync Classes
        $department->classes()->sync($request->class_ids);

        return redirect()->back()->with('success', 'Department updated successfully!');
    }

    public function destroy($id)
    {
        $department = ClassDepartment::findOrFail($id);
        if ($department->icon && File::exists(public_path($department->icon))) File::delete(public_path($department->icon));
        $department->delete();
        return redirect()->back()->with('success', 'Department deleted successfully!');
    }

    public function show($id)
    {
        // Class সহ ডাটা রিটার্ন করবে
        $department = ClassDepartment::with('classes')->findOrFail($id);
        return response()->json($department);
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $order) {
            ClassDepartment::where('id', $order['id'])->update(['serial' => $order['position']]);
        }
        return response()->json(['status' => 'success']);
    }
}