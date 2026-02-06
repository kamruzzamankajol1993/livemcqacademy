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
use App\Models\Section;
class McqQuestionController extends Controller
{


// Board Question Index Page
public function boardQuestionIndex()
{
    $classes = SchoolClass::where('status', 1)->get(); //
    $boards = Board::where('status', 1)->get(); //
    $subjects = Subject::where('status', 1)->get(); // শুরুতে সব সাবজেক্ট দেখানোর জন্য
    
    return view('admin.mcq.board_index', compact('classes', 'boards', 'subjects')); //
}

// AJAX Data for Board Questions
/**
 * Board Question List-এর জন্য AJAX ডাটা রিটার্ন করে
 * * @param \Illuminate\Http\Request $request
 * @return \Illuminate\Http\JsonResponse
 */
public function boardQuestionData(Request $request)
{
    // ১. কুয়েরি শুরু করা এবং রিলেশনগুলো লোড করা
    // শুধুমাত্র সেই প্রশ্নগুলো আনবে যেগুলোতে board_ids ফিল্ডে ডাটা আছে
    $query = McqQuestion::with(['class', 'subject', 'chapter'])
                ->whereNotNull('board_ids')
                ->where('board_ids', '!=', '[]')
                ->where('board_ids', '!=', 'null');

    // ২. বোর্ড ফিল্টার (JSON Column ফিল্টারিং)
    if ($request->filled('board_id')) {
        $query->whereJsonContains('board_ids', $request->board_id);
    }

    // ৩. ক্লাস ফিল্টার
    if ($request->filled('class_id')) {
        $query->where('class_id', $request->class_id);
    }

    // ৪. সাবজেক্ট ফিল্টার
    if ($request->filled('subject_id')) {
        $query->where('subject_id', $request->subject_id);
    }

    // ৫. সার্চ ফিল্টার (প্রশ্নের টেক্সট অনুযায়ী)
    if ($request->filled('search')) {
        $query->where('question', 'like', '%' . $request->search . '%');
    }

    // ৬. সর্টিং এবং প্যাগিনেশন
    $data = $query->orderBy('id', 'desc')->paginate(10);

    // ৭. JSON রেসপন্স রিটার্ন
    return response()->json([
        'data'         => $data->items(),
        'total'        => $data->total(),
        'current_page' => $data->currentPage(),
        'last_page'    => $data->lastPage(),
        'per_page'     => $data->perPage(),
        'from'         => $data->firstItem(),
        'to'           => $data->lastItem(),
    ]);
}

public function instituteQuestionIndex()
{
    $classes = SchoolClass::where('status', 1)->get();
    $institutes = Institute::where('status', 1)->orderBy('name_en', 'asc')->get();
    $subjects = Subject::where('status', 1)->get();
    
    return view('admin.mcq.institute_index', compact('classes', 'institutes', 'subjects'));
}

public function instituteQuestionData(Request $request)
{
    $query = McqQuestion::with(['class', 'subject', 'chapter'])
                ->whereNotNull('institute_ids')
                ->where('institute_ids', '!=', '[]');

    // Institute Filter (JSON Column)
    if ($request->filled('institute_id')) {
        $query->whereJsonContains('institute_ids', $request->institute_id);
    }

    if ($request->filled('class_id')) {
        $query->where('class_id', $request->class_id);
    }

    if ($request->filled('subject_id')) {
        $query->where('subject_id', $request->subject_id);
    }

    if ($request->filled('search')) {
        $query->where('question', 'like', '%' . $request->search . '%');
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


public function getInstitutesByType(Request $request)
    {
        $institutes = Institute::where('type', $request->type)
                               ->where('status', 1)
                               ->orderBy('name_en', 'asc')
                               ->get();
        return response()->json($institutes);
    }
    // --- VIEW PAGES ---

   

   

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

    
    public function create()
    {
        $data = [
            'categories' => Category::where('status', 1)->get(),
            'institutes' => Institute::where('status', 1)->get(),
            'boards'     => Board::where('status', 1)->get(),
            'sections'   => Section::where('status', 1)->get(),
        ];
        return view('admin.mcq.create', $data);
    }

    /**
     * নতুন MCQ ডাটাবেসে সেভ করা
     */
    public function store(Request $request)
    {
        $request->validate([
            'mcq_type'   => 'required',
            'answer'     => 'required',
            'class_id'   => 'required',
            'subject_id' => 'required',
        ]);

        $data = $request->all();

        // ইমেজ হ্যান্ডেলিং (যদি টাইপ ইমেজ হয়)
        if ($request->mcq_type == 'image') {
            foreach (['question', 'option_1', 'option_2', 'option_3', 'option_4'] as $field) {
                if ($request->hasFile($field . '_img')) {
                    $file = $request->file($field . '_img');
                    $name = 'mcq_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/mcq'), $name);
                    $data[$field . '_img'] = 'uploads/mcq/' . $name;
                    $data[$field] = null; // ইমেজ মুডে টেক্সট নাল থাকবে
                }
            }
        }

        $data['status'] = $request->status ?? 1;
        McqQuestion::create($data);

        return redirect()->route('mcq.index')->with('success', 'MCQ Created Successfully');
    }

    /**
     * MCQ এডিট করার পেজ প্রদর্শন
     */
    public function edit($id)
    {
        $mcq = McqQuestion::findOrFail($id);
        $data = [
            'mcq'        => $mcq,
            'categories' => Category::where('status', 1)->get(),
            'institutes' => Institute::where('status', 1)->get(),
            'boards'     => Board::where('status', 1)->get(),
            'sections'   => Section::where('status', 1)->get(),
            'classes'    => SchoolClass::where('status', 1)->get(),
        ];
        return view('admin.mcq.edit', $data);
    }

    /**
     * বিদ্যমান MCQ আপডেট করা
     */
    public function update(Request $request, $id)
    {
        $mcq = McqQuestion::findOrFail($id);
        $data = $request->all();

        // ইমেজ আপডেট লজিক
        if ($request->mcq_type == 'image') {
            foreach (['question', 'option_1', 'option_2', 'option_3', 'option_4'] as $field) {
                if ($request->hasFile($field . '_img')) {
                    // পুরাতন ফাইল ডিলিট করা (যদি থাকে)
                    if ($mcq->{$field . '_img'} && File::exists(public_path($mcq->{$field . '_img'}))) {
                        File::delete(public_path($mcq->{$field . '_img'}));
                    }
                    
                    $file = $request->file($field . '_img');
                    $name = 'mcq_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                    $file->move(public_path('uploads/mcq'), $name);
                    $data[$field . '_img'] = 'uploads/mcq/' . $name;
                    $data[$field] = null;
                }
            }
        }

        $mcq->update($data);
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
    // institute এবং board লোড করা যাবে না কারণ এগুলো সরাসরি relationship নয়
    $mcq = McqQuestion::with(['category', 'class', 'subject', 'chapter', 'topic', 'department', 'section'])->findOrFail($id);
    
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
    // ফাইল ভ্যালিডেশন
    $request->validate([
        'file' => 'required|mimes:xlsx,xls,csv'
    ]);

    try {
        $file = $request->file('file');

        // ১. PhpSpreadsheet ব্যবহার করে ড্রয়িং (ইমেজ) কালেকশন বের করা
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file->getRealPath());
        $spreadsheetObj = $reader->load($file->getRealPath());
        $drawings = $spreadsheetObj->getActiveSheet()->getDrawingCollection();

        // ২. ড্রয়িং অবজেক্টসহ ইম্পোর্ট ক্লাসটি রান করা
        // নোট: আপনার ইম্পোর্ট ক্লাসটি (McqQuestionImport) ড্রয়িং হ্যান্ডেল করার জন্য প্রস্তুত থাকতে হবে
        \Maatwebsite\Excel\Facades\Excel::import(new \App\Imports\McqQuestionImport($drawings), $file);

        return redirect()->back()->with('success', 'MCQs with images and multiple categories imported successfully!');

    } catch (\Exception $e) {
        // কোনো এরর হলে সেটি দেখানো
        return redirect()->back()->with('error', 'Error during import: ' . $e->getMessage());
    }
}
}