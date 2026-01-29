<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SampleAcademicYearExport;
use App\Imports\AcademicYearImport;

class AcademicYearController extends Controller
{
    public function index()
    {
        $academicYears = AcademicYear::orderBy('serial', 'asc')->get();
        return view('admin.academic_year.index', compact('academicYears'));
    }

    public function data(Request $request)
    {
        $query = AcademicYear::query();

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
        ]);

        AcademicYear::create([
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'status' => $request->status ?? 1,
            'serial' => (AcademicYear::max('serial') ?? 0) + 1,
        ]);

        return redirect()->back()->with('success', 'Academic Year created successfully!');
    }

    public function update(Request $request, $id)
    {
        $academicYear = AcademicYear::findOrFail($id);
        $request->validate([
            'name_en' => 'required|string',
            'name_bn' => 'required|string',
        ]);

        $academicYear->update([
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Academic Year updated successfully!');
    }

    public function destroy($id)
    {
        AcademicYear::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Academic Year deleted successfully!');
    }

    public function show($id)
    {
        return response()->json(AcademicYear::findOrFail($id));
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $order) {
            AcademicYear::where('id', $order['id'])->update(['serial' => $order['position']]);
        }
        return response()->json(['status' => 'success']);
    }

    public function downloadSample()
    {
        return Excel::download(new SampleAcademicYearExport, 'academic_year_sample.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate(['file' => 'required|mimes:xlsx,xls,csv']);
        Excel::import(new AcademicYearImport, $request->file('file'));
        return redirect()->back()->with('success', 'Academic Years imported successfully!');
    }
}