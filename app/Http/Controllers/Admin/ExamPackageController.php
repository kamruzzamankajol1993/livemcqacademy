<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamPackage;
use App\Models\SchoolClass;
use App\Models\ClassDepartment;
use App\Models\Subject;
use App\Models\Chapter;
use App\Models\Topic;
use Illuminate\Http\Request;
use App\Models\ExamCategory; // Added this
class ExamPackageController extends Controller
{
    /**
     * ডিসপ্লে লিস্ট এবং এজেক্স ডাটা হ্যান্ডেলিং (Custom Pagination)
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ExamPackage::with(['schoolClass', 'department', 'category']);

            // সার্চ ফিল্টার
            if ($request->filled('search')) {
                $query->where('exam_name', 'like', '%' . $request->search . '%');
            }

            // কাস্টম প্যাগিনেশন রেসপন্স (আপনার আগের মডিউলগুলোর স্টাইলে)
            $data = $query->latest()->paginate(10);

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

        $classes = SchoolClass::where('status', 1)->orderBy('serial', 'asc')->get();
        return view('admin.exam_package.index', compact('classes'));
    }

    /**
     * ক্রিয়েট ব্লেড লোড (AJAX Modal-এর জন্য আলাদা ব্লেড)
     */
   public function create()
{
    $categories = ExamCategory::where('status', 1)->get(); // Added this
    $classes = SchoolClass::where('status', 1)->orderBy('serial', 'asc')->get();
    return view('admin.exam_package.create', compact('classes', 'categories'));
}

public function edit($id)
{
    $package = ExamPackage::findOrFail($id);
    $categories = ExamCategory::where('status', 1)->get(); // Added this
    $classes = SchoolClass::where('status', 1)->get();
    return view('admin.exam_package.edit', compact('package', 'classes', 'categories'));
}

    /**
     * ডাটা সংরক্ষণ
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_id'      => 'required',
            'exam_name'     => 'required|string|max:255',
            'exam_type'     => 'required|in:free,paid',
            'validity_days' => 'required|integer',
            'price'         => 'required_if:exam_type,paid',
        ]);

        // JSON কলামগুলোর জন্য অ্যারে ডাটা সেভ হবে
        ExamPackage::create($request->all());

        return redirect()->back()->with('success', 'Exam Package Created Successfully!');
    }

   

    /**
     * ডাটা আপডেট
     */
    public function update(Request $request, $id)
    {
        $package = ExamPackage::findOrFail($id);
        $package->update($request->all());

        return redirect()->back()->with('success', 'Exam Package Updated!');
    }

    /**
     * ডাটা ডিলিট
     */
    public function destroy($id)
    {
        ExamPackage::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Exam Package Deleted!');
    }
public function show($id)
{
    // প্যাকেজের সাথে প্রয়োজনীয় সব রিলেশনলোড করা
    $package = ExamPackage::with(['schoolClass', 'department'])->findOrFail($id);
    
    return view('admin.exam_package.show', compact('package'));
}
    // ================= AJAX DEPENDENCY DROP DOWN METHODS =================

    /**
     * Class -> Department
     */
    public function getDepartments($class_id)
    {
        $class = SchoolClass::find($class_id);
        if(!$class) return response()->json([]);
        return response()->json($class->departments);
    }

    /**
     * Class/Department -> Subjects
     */
    public function getSubjects(Request $request)
    {
        $query = Subject::query();
        if ($request->filled('department_id') && $request->department_id != 'null') {
            $query->whereHas('departments', function($q) use ($request) {
                $q->where('class_department_id', $request->department_id);
            });
        } else {
            $query->whereHas('classes', function($q) use ($request) {
                $q->where('class_id', $request->class_id);
            });
        }
        return response()->json($query->where('status', 1)->get());
    }

    /**
     * Multiple Subjects -> Chapters
     */
    public function getChapters(Request $request)
    {
        $subjectIds = $request->subject_ids; // Array expected
        $chapters = Chapter::whereIn('subject_id', (array)$subjectIds)
                           ->where('status', 1)
                           ->get();
        return response()->json($chapters);
    }

    /**
     * Multiple Chapters -> Topics
     */
    public function getTopics(Request $request)
    {
        $chapterIds = $request->chapter_ids; // Array expected
        $topics = Topic::whereIn('chapter_id', (array)$chapterIds)
                       ->where('status', 1)
                       ->get();
        return response()->json($topics);
    }
}