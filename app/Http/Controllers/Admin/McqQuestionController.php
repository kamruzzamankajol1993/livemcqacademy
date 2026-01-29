<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\McqQuestion;
use App\Models\Category;
use App\Models\SchoolClass;
use App\Models\ClassDepartment;
use App\Models\Subject;
use App\Models\Chapter;
use App\Models\Topic;
use App\Models\Institute;
use App\Models\Board;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SampleMcqQuestionExport;
use App\Imports\McqQuestionImport;
class McqQuestionController extends Controller
{


public function getInstitutesByType(Request $request)
    {
        $institutes = Institute::where('type', $request->type)
                               ->where('status', 1)
                               ->orderBy('name_en', 'asc')
                               ->get();
        return response()->json($institutes);
    }
    // --- VIEW PAGES ---

    public function create()
    {
        $data = [
            'categories' => Category::where('status', 1)->get(),
            'institutes' => Institute::where('status', 1)->get(),
            'boards'     => Board::where('status', 1)->get(),
            'years'      => AcademicYear::where('status', 1)->get(),
        ];
        return view('admin.mcq.create', $data);
    }

    // --- EDIT PAGE ---
    public function edit($id)
    {
        $mcq = McqQuestion::with('institute')->findOrFail($id);
        
        $data = [
            'mcq'        => $mcq,
            'categories' => Category::where('status', 1)->get(),
            'boards'     => Board::where('status', 1)->get(),
            'years'      => AcademicYear::where('status', 1)->get(),
            
            // Edit পেজের জন্য বর্তমান টাইপের সব ইনস্টিটিউট লোড করা হচ্ছে
            'institutes' => $mcq->institute_id 
                            ? Institute::where('type', $mcq->institute->type)->where('status', 1)->get() 
                            : [],
                            
            'classes'    => SchoolClass::where('status', 1)->get(), 
        ];
        return view('admin.mcq.edit', $data);
    }

    // --- AJAX METHODS FOR DEPENDENCY ---

    public function getClasses(Request $request)
    {
        $classes = SchoolClass::whereHas('categories', function($q) use ($request) {
            $q->where('categories.id', $request->category_id);
        })->where('status', 1)->get();
        return response()->json($classes);
    }

    public function getDepartments(Request $request)
    {
        // ক্লাস অনুযায়ী ডিপার্টমেন্ট
        $departments = ClassDepartment::whereHas('classes', function($q) use ($request) {
            $q->where('school_classes.id', $request->class_id);
        })->where('status', 1)->get();
        return response()->json($departments);
    }

    public function getSubjects(Request $request)
    {
        $classId = $request->class_id;
        $deptId = $request->department_id;

        $query = Subject::query()->where('status', 1);

        // 1. Filter by Class (Must)
        $query->whereHas('classes', function($q) use ($classId) {
            $q->where('school_classes.id', $classId);
        });

        // 2. Filter by Department (If provided)
        if (!empty($deptId)) {
            $query->whereHas('departments', function($q) use ($deptId) {
                $q->where('class_departments.id', $deptId);
            });
        }

        $subjects = $query->get();
        return response()->json($subjects);
    }

    public function getChapters(Request $request)
{
    $query = Chapter::where('subject_id', $request->subject_id)
                    ->where('status', 1);

    // যদি class_id রিকোয়েস্টে আসে, তবে সেটা দিয়েও ফিল্টার হবে
    if ($request->has('class_id') && !empty($request->class_id)) {
        $query->where('class_id', $request->class_id);
    }

    $chapters = $query->orderBy('serial', 'asc')->get();
    
    return response()->json($chapters);
}

    public function getTopics(Request $request)
    {
        $topics = Topic::where('chapter_id', $request->chapter_id)
                       ->where('status', 1)->orderBy('serial', 'asc')->get();
        return response()->json($topics);
    }

    // --- STORE & UPDATE ---

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required',
            'option_1' => 'required',
            'answer'   => 'required',
            'class_id' => 'required',
            'subject_id' => 'required',
        ]);

        McqQuestion::create($request->all() + ['status' => $request->status ?? 1]);

        return redirect()->route('mcq.index')->with('success', 'MCQ Created Successfully');
    }

    public function update(Request $request, $id)
    {
        $mcq = McqQuestion::findOrFail($id);
        $request->validate([
            'question' => 'required',
            'class_id' => 'required',
            'subject_id' => 'required',
        ]);

        $mcq->update($request->all());

        return redirect()->route('mcq.index')->with('success', 'MCQ Updated Successfully');
    }

    // --- INDEX PAGE ---
    public function index()
    {
        // ফিল্টারিং এর জন্য ড্রপডাউন ডাটা পাঠানো হচ্ছে
        $classes = SchoolClass::where('status', 1)->get();
        $subjects = Subject::where('status', 1)->get();
        return view('admin.mcq.index', compact('classes', 'subjects'));
    }

    // --- AJAX DATA FOR INDEX TABLE ---
    public function data(Request $request)
    {
        $query = McqQuestion::with(['class', 'subject', 'chapter']);

        // Filters
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('subject_id')) {
            $query->where('subject_id', $request->subject_id);
        }
        if ($request->filled('search')) {
            $query->where('question', 'like', '%' . $request->search . '%');
        }

        $sort = $request->get('sort', 'id');
        $direction = $request->get('direction', 'desc');
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

    // --- SHOW PAGE ---
    public function show($id)
    {
        $mcq = McqQuestion::with(['category', 'class', 'subject', 'chapter', 'topic', 'institute', 'board', 'academicYear'])->findOrFail($id);
        return view('admin.mcq.show', compact('mcq'));
    }

    // --- DELETE ---
    public function destroy($id)
    {
        McqQuestion::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'MCQ deleted successfully!');
    }

    // --- EXPORT SAMPLE ---
    public function downloadSample()
    {
        return Excel::download(new SampleMcqQuestionExport, 'mcq_sample.xlsx');
    }

    // --- IMPORT ---
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        try {
            Excel::import(new McqQuestionImport, $request->file('file'));
            return redirect()->back()->with('success', 'MCQs imported successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}