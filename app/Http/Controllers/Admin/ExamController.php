<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Exam;
use App\Models\ExamCategory;
use Illuminate\Http\Request;

class ExamController extends Controller
{
    /**
     * ডিসপ্লে লিস্ট এবং এজেক্স ডাটা হ্যান্ডেলিং
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Exam মডেলের এক্সেসর (category_names) ব্যবহার করা হবে টেবিলে নাম দেখানোর জন্য
            $query = Exam::query();

            // সার্চ ফিল্টার: এখানে মূলত আইডি বা মার্কস দিয়ে ফিল্টার করা সহজ
            if ($request->filled('search')) {
                $query->where('total_questions', 'like', '%' . $request->search . '%');
            }

            $data = $query->latest()->paginate(10);

            // ম্যানুয়ালি ক্যাটাগরি নামগুলো যুক্ত করা রেসপন্সে (যদি এক্সেসর অ্যাপেন্ড করা না থাকে)
            $items = collect($data->items())->map(function($exam) {
                $exam->category_names = $exam->category_names; // মডেলের এক্সেসর কল
                return $exam;
            });

            return response()->json([
                'data'         => $items,
                'total'        => $data->total(),
                'current_page' => $data->currentPage(),
                'last_page'    => $data->lastPage(),
                'per_page'     => $data->perPage(),
                'from'         => $data->firstItem(),
                'to'           => $data->lastItem(),
            ]);
        }

        $categories = ExamCategory::where('status', 1)->orderBy('serial', 'asc')->get();
        return view('admin.exam.index', compact('categories'));
    }

    /**
     * Create ব্লেড ফাইলটি রিটার্ন করবে (AJAX Modal-এর জন্য)
     */
    public function create()
    {
        $categories = ExamCategory::where('status', 1)->orderBy('serial', 'asc')->get();
        return view('admin.exam.create', compact('categories'))->render();
    }

    /**
     * নতুন এক্সাম কনফিগারেশন সেভ করা
     */
    public function store(Request $request)
    {
        $request->validate([
            'exam_category_ids'     => 'required|array',
            'negative_marks'        => 'required|array',
            'total_questions'       => 'required|integer|min:1',
            'per_question_mark'     => 'required|numeric|min:0.1',
            'pass_mark'             => 'required|numeric',
            'exam_duration_minutes' => 'required|integer|min:1',
        ]);

        // মডেলের $casts প্রপার্টি অ্যারেগুলোকে অটোমেটিক JSON এ কনভার্ট করবে
        Exam::create($request->all());

        return redirect()->back()->with('success', 'Exam configuration saved successfully!');
    }

    /**
     * Edit ব্লেড ফাইলটি ডাটা সহ রিটার্ন করবে (AJAX Modal-এর জন্য)
     */
    public function edit($id)
    {
        $exam = Exam::findOrFail($id);
        $categories = ExamCategory::where('status', 1)->orderBy('serial', 'asc')->get();
        
        // এডিট ব্লেডটি রিটার্ন করা হচ্ছে যাতে মোডালের ভেতর সরাসরি ফর্মটি লোড করা যায়
        return view('admin.exam.edit', compact('exam', 'categories'))->render();
    }

    /**
     * বিদ্যমান কনফিগারেশন আপডেট করা
     */
    public function update(Request $request, $id)
    {
        $exam = Exam::findOrFail($id);

        $request->validate([
            'exam_category_ids'     => 'required|array',
            'negative_marks'        => 'required|array',
            'total_questions'       => 'required|integer|min:1',
            'per_question_mark'     => 'required|numeric|min:0.1',
            'pass_mark'             => 'required|numeric',
            'exam_duration_minutes' => 'required|integer|min:1',
        ]);

        $exam->update($request->all());

        return redirect()->back()->with('success', 'Exam configuration updated successfully!');
    }

    /**
     * ডাটা ডিলিট করা
     */
    public function destroy($id)
    {
        Exam::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Configuration deleted successfully!');
    }

    /**
     * স্ট্যাটাস আপডেট করার জন্য (Optional)
     */
    public function toggleStatus($id)
    {
        $exam = Exam::findOrFail($id);
        $exam->status = $exam->status == 1 ? 0 : 1;
        $exam->save();

        return response()->json(['status' => 'success']);
    }
}