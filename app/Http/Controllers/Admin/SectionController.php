<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Category;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SampleSectionExport;
use App\Imports\SectionImport;

class SectionController extends Controller
{
    /**
     * Display the main view.
     */
    public function index()
    {
        // Drag & Drop এর জন্য সিরিয়াল অনুযায়ী ডাটা আনা হচ্ছে
        $sections = Section::with(['category', 'class', 'subject'])
                           ->orderBy('serial', 'asc')
                           ->get();
        
        // মোডালের ড্রপডাউনের জন্য ডাটা
        $categories = Category::where('status', 1)->get();
        $classes = SchoolClass::where('status', 1)->get();
        $subjects = Subject::where('status', 1)->get();

        return view('admin.section.index', compact('sections', 'categories', 'classes', 'subjects'));
    }

    /**
     * Fetch data for AJAX Data Table.
     */
    public function data(Request $request)
    {
        $query = Section::with(['category', 'class', 'subject']);

        if ($request->filled('search')) {
            $query->where('name_en', 'like', $request->search . '%')
                  ->orWhere('name_bn', 'like', $request->search . '%');
        }

        // ডিফল্ট সর্টিং সিরিয়াল অনুযায়ী, তবে ইউজার চাইলে পরিবর্তন করতে পারবে
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string',
            'name_bn' => 'required|string',
            'class_id' => 'required', // Class is mandatory based on context
        ]);

        Section::create([
            'category_id' => $request->category_id,
            'class_id'    => $request->class_id,
            'subject_id'  => $request->subject_id,
            'name_en'     => $request->name_en,
            'name_bn'     => $request->name_bn,
            'status'      => $request->status ?? 1,
            // নতুন ডাটা সবার শেষে যোগ হবে
            'serial'      => (Section::max('serial') ?? 0) + 1,
        ]);

        return redirect()->back()->with('success', 'Section created successfully!');
    }

    /**
     * Fetch a single resource for Editing (AJAX).
     */
    public function show($id)
    {
        $section = Section::findOrFail($id);
        return response()->json($section);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $section = Section::findOrFail($id);
        
        $request->validate([
            'name_en' => 'required|string',
            'name_bn' => 'required|string',
            'class_id' => 'required',
        ]);

        $section->update([
            'category_id' => $request->category_id,
            'class_id'    => $request->class_id,
            'subject_id'  => $request->subject_id,
            'name_en'     => $request->name_en,
            'name_bn'     => $request->name_bn,
            'status'      => $request->status,
        ]);

        return redirect()->back()->with('success', 'Section updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Section::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Section deleted successfully!');
    }

    /**
     * Update the serial order (Drag & Drop).
     */
    public function reorder(Request $request)
    {
        if($request->has('order')) {
            foreach ($request->order as $order) {
                Section::where('id', $order['id'])->update(['serial' => $order['position']]);
            }
            return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'error'], 400);
    }

    /**
     * Download Sample Excel File.
     */
    public function downloadSample()
    {
        return Excel::download(new SampleSectionExport, 'section_sample.xlsx');
    }

    /**
     * Import Excel File.
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv'
        ]);

        try {
            Excel::import(new SectionImport, $request->file('file'));
            return redirect()->back()->with('success', 'Sections imported successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function getClassesByCategory(Request $request)
{
    // Category এর সাথে সম্পর্কিত Class গুলো খুঁজে বের করা (Pivot Table ব্যবহার করে)
    $classes = SchoolClass::whereHas('categories', function($q) use ($request) {
        $q->where('categories.id', $request->category_id);
    })->where('status', 1)->get();
    
    return response()->json($classes);
}

public function getSubjectsByClass(Request $request)
{
    // Class এর সাথে সম্পর্কিত Subject গুলো খুঁজে বের করা
    $subjects = Subject::whereHas('classes', function($q) use ($request) {
        $q->where('school_classes.id', $request->class_id);
    })->where('status', 1)->get();

    return response()->json($subjects);
}
}