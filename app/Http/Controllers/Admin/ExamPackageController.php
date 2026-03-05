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
use Carbon\Carbon;
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
$boards = \App\Models\Board::where('status', 1)->get();
    $institutes = \App\Models\Institute::where('status', 1)->get();

    $categories = ExamCategory::where('status', 1)->whereNotIn('id',[1,3])->get(); // Added this
    $classes = SchoolClass::where('status', 1)->orderBy('serial', 'asc')->get();
    return view('admin.exam_package.create', compact('boards', 'institutes','classes', 'categories'));
}

public function edit($id)
{
$boards = \App\Models\Board::where('status', 1)->get();
    $institutes = \App\Models\Institute::where('status', 1)->get();

    $package = ExamPackage::findOrFail($id);
    $categories = ExamCategory::where('status', 1)->whereNotIn('id',[1,3])->get(); // Added this
    $classes = SchoolClass::where('status', 1)->get();
    return view('admin.exam_package.edit', compact('boards', 'institutes','package', 'classes', 'categories'));
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
            'start_time' => 'required|date',
    'end_time'   => 'required|date|after:start_time',
            'price'         => 'required_if:exam_type,paid',
        ]);

        // ডাটা ধরার পর নির্দিষ্ট টাইমজোনে কনভার্ট করা
    $data = $request->all();
    
    // Asia/Dhaka টাইমজোনে সেট করা
    $data['start_time'] = Carbon::parse($request->start_time)->timezone('Asia/Dhaka');
    $data['end_time']   = Carbon::parse($request->end_time)->timezone('Asia/Dhaka');

        // JSON কলামগুলোর জন্য অ্যারে ডাটা সেভ হবে
        ExamPackage::create($data);

        return redirect()->back()->with('success', 'Exam Package Created Successfully!');
    }

   

    /**
     * ডাটা আপডেট
     */
    public function update(Request $request, $id)
{
    $request->validate([
        'start_time' => 'required|date',
        'end_time'   => 'required|date|after:start_time',
    ]);

    $package = \App\Models\ExamPackage::findOrFail($id);
    
    $data = $request->all();
    
    // আপডেট করার সময়ও টাইমজোন নিশ্চিত করা
    $data['start_time'] = Carbon::parse($request->start_time)->timezone('Asia/Dhaka');
    $data['end_time']   = Carbon::parse($request->end_time)->timezone('Asia/Dhaka');

    $package->update($data);

    return redirect()->back()->with('success', 'Package updated successfully!');
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

    public function viewLeaderboard(Request $request, $id)
{
    $package = ExamPackage::findOrFail($id);

    // যদি AJAX রিকোয়েস্ট হয় (টেবিল লোড করার জন্য)
    if ($request->ajax()) {
        $query = \App\Models\ExamResult::with('user:id,name,phone')
                    ->where('exam_package_id', $id);

        // সার্চ ফিল্টার
        if ($request->filled('search')) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        // কাস্টম প্যাগিনেশন রেসপন্স
        $data = $query->orderBy('earned_marks', 'desc')->paginate(15);

        return response()->json([
            'data'         => $data->items(),
            'total'        => $data->total(),
            'current_page' => $data->currentPage(),
            'last_page'    => $data->lastPage(),
            'from'         => $data->firstItem(),
            'to'           => $data->lastItem(),
        ]);
    }

    return view('admin.exam_package.leaderboard', compact('package'));
}
}