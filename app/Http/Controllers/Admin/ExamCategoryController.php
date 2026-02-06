<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExamCategory;
use Illuminate\Http\Request;

class ExamCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = ExamCategory::query();

            if ($request->filled('search')) {
                $query->where('name_en', 'like', '%' . $request->search . '%')
                      ->orWhere('name_bn', 'like', '%' . $request->search . '%');
            }

            // ড্র্যাগ এন্ড ড্রপ ট্যাবের জন্য সব ডাটা একসাথে লাগবে
            if ($request->has('all_data')) {
                $categories = ExamCategory::orderBy('serial', 'asc')->get();
                return response()->json($categories);
            }

            // মেইন টেবিলের জন্য প্যাগিনেশন
            $data = $query->orderBy('serial', 'asc')->paginate(10);
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

        return view('admin.exam_category.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
        ]);

        $maxSerial = ExamCategory::max('serial') ?? 0;

        ExamCategory::create([
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'status'  => $request->status ?? 1,
            'serial'  => $maxSerial + 1,
        ]);

        return redirect()->back()->with('success', 'Category Created Successfully');
    }

    public function edit($id)
    {
        return response()->json(ExamCategory::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $category = ExamCategory::findOrFail($id);
        $category->update($request->only('name_en', 'name_bn', 'status'));
        return redirect()->back()->with('success', 'Category Updated Successfully');
    }

    public function destroy($id)
    {
        ExamCategory::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Category Deleted Successfully');
    }

    public function reorder(Request $request)
    {
        foreach ($request->order as $index => $id) {
            ExamCategory::where('id', $id)->update(['serial' => $index + 1]);
        }
        return response()->json(['status' => 'success', 'message' => 'Order Updated Successfully']);
    }
}