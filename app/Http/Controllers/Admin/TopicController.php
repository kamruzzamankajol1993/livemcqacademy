<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Topic;
use App\Models\Subject;
use App\Models\Chapter;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SampleTopicExport;
use App\Imports\TopicImport;
use App\Models\SchoolClass;
class TopicController extends Controller
{
    public function index()
    {
        // Class সহ ডাটা আনা হলো
        $topics = Topic::with(['class', 'subject', 'chapter'])->orderBy('serial', 'asc')->get();
        
        // ভিউতে শুধু Class পাঠানো হবে, বাকিরা AJAX দিয়ে আসবে
        $classes = SchoolClass::where('status', 1)->get();

        return view('admin.topic.index', compact('topics', 'classes'));
    }

    public function data(Request $request)
    {
        $query = Topic::with(['class', 'subject', 'chapter']); // Class relation added

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

    // --- NEW AJAX METHOD ---
    public function getChaptersByClassAndSubject(Request $request)
    {
        // Class ID এবং Subject ID দিয়ে ফিল্টার করে চ্যাপ্টার আনা হচ্ছে
        $chapters = Chapter::where('class_id', $request->class_id)
                           ->where('subject_id', $request->subject_id)
                           ->where('status', 1)
                           ->get();
        return response()->json($chapters);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string',
            'name_bn' => 'required|string',
            'class_id' => 'required', // Validation
            'subject_id' => 'required',
            'chapter_id' => 'required',
        ]);

        Topic::create([
            'class_id' => $request->class_id, // Added
            'subject_id' => $request->subject_id,
            'chapter_id' => $request->chapter_id,
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'status' => $request->status ?? 1,
            'serial' => (Topic::max('serial') ?? 0) + 1,
        ]);

        return redirect()->back()->with('success', 'Topic created successfully!');
    }

    public function update(Request $request, $id)
    {
        $topic = Topic::findOrFail($id);
        $request->validate([
            'name_en' => 'required|string',
            'name_bn' => 'required|string',
            'class_id' => 'required', // Validation
            'subject_id' => 'required',
            'chapter_id' => 'required',
        ]);

        $topic->update([
            'class_id' => $request->class_id, // Added
            'subject_id' => $request->subject_id,
            'chapter_id' => $request->chapter_id,
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Topic updated successfully!');
    }

    public function destroy($id)
    {
        Topic::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Topic deleted successfully!');
    }

    public function show($id)
    {
        $topic = Topic::findOrFail($id);
        return response()->json($topic);
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $order) {
            Topic::where('id', $order['id'])->update(['serial' => $order['position']]);
        }
        return response()->json(['status' => 'success']);
    }

    public function downloadSample()
    {
        return Excel::download(new SampleTopicExport, 'topic_sample.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        try {
            Excel::import(new TopicImport, $request->file('file'));
            return redirect()->back()->with('success', 'Topics imported successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}