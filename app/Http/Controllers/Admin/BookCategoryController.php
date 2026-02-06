<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookCategory;
use Illuminate\Http\Request;

class BookCategoryController extends Controller
{
    /**
     * মেইন ইনডেক্স পেজ (এখান থেকেই মোডাল কন্ট্রোল হবে)
     */
    public function index()
    {
        return view('admin.book_category.index');
    }

    /**
     * AJAX ডাটা ফেচিং (টেবিল এবং কাস্টম প্যাগিনেশনের জন্য)
     */
   public function fetchData(Request $request)
{
    $query = BookCategory::query();

    // সার্চ ফিল্টারিং
    if ($request->filled('search')) {
        $query->where('name_en', 'like', '%' . $request->search . '%')
              ->orWhere('name_bn', 'like', '%' . $request->search . '%');
    }

    // ড্র্যাগ অ্যান্ড ড্রপ (Reorder) ট্যাবের জন্য - এটি একটি সাধারণ অ্যারে পাঠাবে
    if ($request->has('all_data')) {
        $allData = $query->orderBy('serial', 'asc')->get();
        return response()->json($allData); 
    }

    // টেবিল ভিউ (List View) এর জন্য - এটি স্ট্যান্ডার্ড প্যাগিনেশন অবজেক্ট পাঠাবে
    $data = $query->orderBy('serial', 'asc')->paginate(1);
    return response()->json($data);
}

    /**
     * ডাটা সংরক্ষণ (Normal Submit from Modal)
     */
    public function store(Request $request)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
        ]);

        $maxSerial = BookCategory::max('serial') ?? 0;

        BookCategory::create([
            'name_en' => $request->name_en,
            'name_bn' => $request->name_bn,
            'serial'  => $maxSerial + 1,
            'status'  => $request->status ?? 1,
        ]);

        return redirect()->back()->with('success', 'Book Category created successfully!');
    }

    /**
     * এডিট ডাটা ফেচ (শুধুমাত্র মোডাল পপুলেট করার জন্য AJAX)
     */
    public function edit($id)
    {
        $category = BookCategory::findOrFail($id);
        return response()->json($category);
    }

    /**
     * ডাটা আপডেট (Normal Submit from Modal)
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name_en' => 'required|string|max:255',
            'name_bn' => 'nullable|string|max:255',
            'status'  => 'required|in:1,0',
        ]);

        $category = BookCategory::findOrFail($id);
        $category->update($request->all());

        return redirect()->back()->with('success', 'Book Category updated successfully!');
    }

    /**
     * ডাটা ডিলিট (Normal Submit with SweetAlert Confirmation)
     */
    public function destroy($id)
    {
        $category = BookCategory::findOrFail($id);
        $category->delete();

        return redirect()->back()->with('success', 'Book Category deleted successfully!');
    }

    public function reorder(Request $request)
    {
        $request->validate([
            'order' => 'required|array',
        ]);

        // ড্র্যাগ অ্যান্ড ড্রপ সিরিয়াল আপডেট লজিক
        foreach ($request->order as $index => $id) {
            BookCategory::where('id', $id)->update(['serial' => $index + 1]);
        }

        return response()->json(['message' => 'Serial Updated Successfully!']);
    }
}