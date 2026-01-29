<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Chapter;
use App\Models\Subject;
use App\Models\Section;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SampleChapterExport;
use App\Imports\ChapterImport;
use App\Models\SchoolClass;

class ChapterController extends Controller
{
   public function index()
    {
        // class রিলেশন লোড করা হলো
        $chapters = Chapter::with(['class', 'subject', 'section'])->orderBy('serial', 'asc')->get();
        
        // এখন শুধু Classes পাঠাবো, Subject/Section আসবে AJAX দিয়ে
        $classes = SchoolClass::where('status', 1)->get(); 

        return view('admin.chapter.index', compact('chapters', 'classes'));
    }

    public function data(Request $request)
    {
        // class রিলেশন যোগ করা হলো
        $query = Chapter::with(['class', 'subject', 'section']);

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
            'from' => $data->firstItem(), // Fix pagination info
            'to' => $data->lastItem(),
        ]);
    }

    // AJAX: Subject সিলেক্ট করলে সেই ক্লাস ও সাবজেক্টের Section আসবে
    public function getSectionsByClassAndSubject(Request $request)
    {
        $sections = Section::where('class_id', $request->class_id)
                           ->where('subject_id', $request->subject_id)
                           ->where('status', 1)
                           ->get();
        return response()->json($sections);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string',
            'name_bn' => 'required|string',
            'class_id' => 'required', // Validation Added
            'subject_id' => 'required',
        ]);

        Chapter::create([
            'class_id' => $request->class_id, // Added
            'subject_id' => $request->subject_id,
            'section_id' => $request->section_id,
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'status' => $request->status ?? 1,
            'serial' => (Chapter::max('serial') ?? 0) + 1,
        ]);

        return redirect()->back()->with('success', 'Chapter created successfully!');
    }

    public function update(Request $request, $id)
    {
        $chapter = Chapter::findOrFail($id);
        $request->validate([
            'name_en' => 'required|string',
            'name_bn' => 'required|string',
            'class_id' => 'required', // Validation Added
            'subject_id' => 'required',
        ]);

        $chapter->update([
            'class_id' => $request->class_id, // Added
            'subject_id' => $request->subject_id,
            'section_id' => $request->section_id,
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Chapter updated successfully!');
    }

    public function destroy($id)
    {
        Chapter::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Chapter deleted successfully!');
    }

    public function show($id)
    {
        $chapter = Chapter::findOrFail($id);
        return response()->json($chapter);
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $order) {
            Chapter::where('id', $order['id'])->update(['serial' => $order['position']]);
        }
        return response()->json(['status' => 'success']);
    }

    public function downloadSample()
    {
        return Excel::download(new SampleChapterExport, 'chapter_sample.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        try {
            Excel::import(new ChapterImport, $request->file('file'));
            return redirect()->back()->with('success', 'Chapters imported successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }
}