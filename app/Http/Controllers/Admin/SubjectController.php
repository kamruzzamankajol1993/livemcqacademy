<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\SchoolClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Intervention\Image\Laravel\Facades\Image;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SampleSubjectExport;
use App\Imports\SubjectImport;
use App\Models\ClassDepartment;
class SubjectController extends Controller
{
    public function index()
    {
    // departments রিলেশন লোড করা হলো
        $subjects = Subject::with(['classes', 'parent', 'departments'])->orderBy('serial', 'asc')->get();
        $parents = Subject::where('status', 1)->get();
        $classes = SchoolClass::where('status', 1)->get();
        // শুরুতে সব ডিপার্টমেন্ট লোড করে রাখা হলো (অপশনাল)
        $departments = ClassDepartment::where('status', 1)->get(); 
        
        return view('admin.subject.index', compact('subjects', 'parents', 'classes', 'departments'));
    }

    // AJAX: ক্লাস সিলেক্ট করলে ডিপার্টমেন্ট আসবে
    public function getDepartmentsByClasses(Request $request)
    {
        // ক্লাস আইডি এর অ্যারে আসবে
        $classIds = $request->class_ids;

        if (empty($classIds)) {
            return response()->json([]);
        }

        // যে ক্লাসগুলো সিলেক্ট করা হয়েছে, তাদের সাথে সম্পর্কিত ডিপার্টমেন্টগুলো খুঁজে বের করা
        $departments = ClassDepartment::whereHas('classes', function($q) use ($classIds) {
            $q->whereIn('school_classes.id', $classIds);
        })->where('status', 1)->get();

        return response()->json($departments);
    }

    public function data(Request $request)
    {
        // departments রিলেশন অ্যাড করা হলো
        $query = Subject::with(['classes', 'parent', 'departments']);

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
            'class_ids' => 'required|array',
            'department_ids' => 'nullable|array', // New Validation
            'icon' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        $path = null;
        if ($request->hasFile('icon')) {
            $image = $request->file('icon');
            $imageName = 'sub_'.time().'.'.$image->getClientOriginalExtension();
            $folder = 'uploads/subjects/';
            if (!File::isDirectory(public_path($folder))) File::makeDirectory(public_path($folder), 0777, true, true);
            Image::read($image->getRealPath())->resize(50, 50)->save(public_path($folder.$imageName));
            $path = $folder.$imageName;
        }

        $subject = Subject::create([
            'parent_id' => $request->parent_id,
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'color' => $request->color,
            'icon' => $path,
            'status' => $request->status ?? 1,
            'serial' => (Subject::max('serial') ?? 0) + 1,
        ]);

        $subject->classes()->sync($request->class_ids);
        
        // Departments Sync
        if($request->has('department_ids')){
            $subject->departments()->sync($request->department_ids);
        }

        return redirect()->back()->with('success', 'Subject created successfully!');
    }

    public function show($id)
    {
        // departments রিলেশন সহ রিটার্ন
        $subject = Subject::with(['classes', 'departments'])->findOrFail($id);
        return response()->json($subject);
    }

    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);
        $request->validate([
            'name_en' => 'required|string',
            'name_bn' => 'required|string',
            'class_ids' => 'required|array',
            'department_ids' => 'nullable|array', // New
        ]);

        $path = $subject->icon;
        if ($request->hasFile('icon')) {
            if ($subject->icon && File::exists(public_path($subject->icon))) File::delete(public_path($subject->icon));
            $image = $request->file('icon');
            $imageName = 'sub_'.time().'.'.$image->getClientOriginalExtension();
            $folder = 'uploads/subjects/';
            if (!File::isDirectory(public_path($folder))) File::makeDirectory(public_path($folder), 0777, true, true);
            Image::read($image->getRealPath())->resize(50, 50)->save(public_path($folder.$imageName));
            $path = $folder.$imageName;
        }

        $subject->update([
            'parent_id' => $request->parent_id,
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'color' => $request->color,
            'icon' => $path,
            'status' => $request->status,
        ]);

        $subject->classes()->sync($request->class_ids);
        
        // Departments Sync
        if($request->has('department_ids')){
            $subject->departments()->sync($request->department_ids);
        } else {
            $subject->departments()->detach(); // কিছু সিলেক্ট না করলে সব মুছে যাবে
        }

        return redirect()->back()->with('success', 'Subject updated successfully!');
    }

    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);
        if ($subject->icon && File::exists(public_path($subject->icon))) File::delete(public_path($subject->icon));
        $subject->delete();
        return redirect()->back()->with('success', 'Subject deleted successfully!');
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $order) {
            Subject::where('id', $order['id'])->update(['serial' => $order['position']]);
        }
        return response()->json(['status' => 'success']);
    }

    public function downloadSample()
    {
        return Excel::download(new SampleSubjectExport, 'subject_sample.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        try {
            Excel::import(new SubjectImport, $request->file('file'));
            return redirect()->back()->with('success', 'Subjects imported successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}