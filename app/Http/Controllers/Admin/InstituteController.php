<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institute;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SampleInstituteExport;
use App\Imports\InstituteImport;

class InstituteController extends Controller
{
    public function index()
    {
        // প্রতিটি টাইপের জন্য আলাদা কালেকশন পাঠানো হচ্ছে (সর্টিং ভিউয়ের জন্য)
        $schools = Institute::where('type', 'school')->orderBy('serial', 'asc')->get();
        $colleges = Institute::where('type', 'college')->orderBy('serial', 'asc')->get();
        $universities = Institute::where('type', 'university')->orderBy('serial', 'asc')->get();

        return view('admin.institute.index', compact('schools', 'colleges', 'universities'));
    }

    // AJAX Data Table (Type ভিত্তিক ফিল্টারিং)
    public function data(Request $request)
    {
        $query = Institute::query();

        if ($request->has('type') && !empty($request->type)) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name_en', 'like', $request->search . '%')
                  ->orWhere('name_bn', 'like', $request->search . '%');
            });
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
            'type' => 'required|in:school,college,university',
        ]);

        Institute::create([
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'type' => $request->type,
            'status' => $request->status ?? 1,
            'serial' => (Institute::where('type', $request->type)->max('serial') ?? 0) + 1,
        ]);

        return redirect()->back()->with('success', 'Institute created successfully!');
    }

    public function update(Request $request, $id)
    {
        $institute = Institute::findOrFail($id);
        $request->validate([
            'name_en' => 'required|string',
            'name_bn' => 'required|string',
            'type' => 'required|in:school,college,university',
        ]);

        $institute->update([
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'type' => $request->type,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Institute updated successfully!');
    }

    public function destroy($id)
    {
        Institute::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Institute deleted successfully!');
    }

    public function show($id)
    {
        return response()->json(Institute::findOrFail($id));
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $order) {
            Institute::where('id', $order['id'])->update(['serial' => $order['position']]);
        }
        return response()->json(['status' => 'success']);
    }

    public function downloadSample()
    {
        return Excel::download(new SampleInstituteExport, 'institute_sample.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        Excel::import(new InstituteImport, $request->file('file'));
        return redirect()->back()->with('success', 'Institutes imported successfully!');
    }
}