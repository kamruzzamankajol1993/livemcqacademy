<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class BookController extends Controller
{
    /**
     * ডিসপ্লে লিস্ট (AJAX Table)
     */
    public function index()
{
    // ফিল্টার ড্রপডাউনের জন্য প্রয়োজনীয় ডাটা ফেচ করা
    $categories = BookCategory::where('status', 1)->orderBy('name_en', 'asc')->get();
    $classes = SchoolClass::where('status', 1)->orderBy('serial', 'asc')->get();
    $subjects = Subject::where('status', 1)->orderBy('name_en', 'asc')->get();

    return view('admin.book.index', compact('categories', 'classes', 'subjects'));
}

   public function fetchData(Request $request)
{
    $query = Book::with(['category', 'subject', 'schoolClasses']);

    // সার্চ ফিল্টার
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('title', 'like', '%' . $request->search . '%')
              ->orWhere('isbn_code', 'like', '%' . $request->search . '%');
        });
    }

    // ক্লাস ফিল্টার (Many-to-Many রিলেশন)
    if ($request->filled('class_id')) {
        $query->whereHas('schoolClasses', function($q) use ($request) {
            $q->where('school_class_id', $request->class_id);
        });
    }

    // সাবজেক্ট ফিল্টার
    if ($request->filled('subject_id')) {
        $query->where('subject_id', $request->subject_id);
    }

    // ক্যাটাগরি ফিল্টার
    if ($request->filled('category_id')) {
        $query->where('book_category_id', $request->category_id);
    }

    $data = $query->latest()->paginate(10);
    return response()->json($data);
}

    /**
     * ক্রিয়েট পেজ
     */
    public function create()
    {
        $categories = BookCategory::where('status', 1)->get();
        $classes = SchoolClass::where('status', 1)->orderBy('serial', 'asc')->get();
        return view('admin.book.create', compact('categories', 'classes'));
    }

    /**
     * AJAX: ক্লাস অনুযায়ী সাবজেক্ট লোড করা
     */
    public function getSubjectsByClass(Request $request)
{
    $classIds = $request->class_ids; // এটি একটি অ্যারে

    if (!$classIds) {
        return response()->json([]);
    }

    // আপনার মাইগ্রেশন অনুযায়ী 'class_id' ব্যবহার করা হয়েছে
    $subjects = Subject::whereHas('classes', function($q) use ($classIds) {
        $q->whereIn('class_id', $classIds); 
    })
    ->where('status', 1)
    ->orderBy('serial', 'asc')
    ->get(['id', 'name_en', 'name_bn']);

    return response()->json($subjects);
}

    /**
     * ডাটা সংরক্ষণ
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'book_category_id' => 'required',
            'school_class_ids' => 'required|array',
            'image'            => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'preview_pdf'      => 'nullable|mimes:pdf|max:10240',
            'full_pdf'         => 'nullable|mimes:pdf|max:51200',
        ]);

        $data = $request->except(['school_class_ids', 'image', 'preview_pdf', 'full_pdf']);

        // ফাইল আপলোড লজিক
        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadFile($request->file('image'), 'uploads/books/images');
        }
        if ($request->hasFile('preview_pdf')) {
            $data['preview_pdf'] = $this->uploadFile($request->file('preview_pdf'), 'uploads/books/previews');
        }
        if ($request->hasFile('full_pdf')) {
            $data['full_pdf'] = $this->uploadFile($request->file('full_pdf'), 'uploads/books/full');
        }

        $book = Book::create($data); // Model Boot স্লাগ তৈরি করবে
        $book->schoolClasses()->attach($request->school_class_ids);

        return redirect()->route('book.index')->with('success', 'Book saved successfully!');
    }

    /**
     * ডিটেইলস ভিউ
     */
    public function show($id)
    {
        $book = Book::with(['category', 'subject', 'schoolClasses'])->findOrFail($id);
        return view('admin.book.show', compact('book'));
    }

    /**
     * এডিট পেজ
     */
    public function edit($id)
    {
        $book = Book::with('schoolClasses')->findOrFail($id);
        $categories = BookCategory::where('status', 1)->get();
        $classes = SchoolClass::where('status', 1)->get();
        $selectedClasses = $book->schoolClasses->pluck('id')->toArray();

        return view('admin.book.edit', compact('book', 'categories', 'classes', 'selectedClasses'));
    }

    /**
     * ডাটা আপডেট
     */
    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        $data = $request->except(['school_class_ids', 'image', 'preview_pdf', 'full_pdf']);

        // ইমেজ আপডেট ও পুরাতন ফাইল ডিলিট
        if ($request->hasFile('image')) {
            $this->deleteOldFile($book->image);
            $data['image'] = $this->uploadFile($request->file('image'), 'uploads/books/images');
        }

        // পিডিএফ আপডেট
        if ($request->hasFile('preview_pdf')) {
            $this->deleteOldFile($book->preview_pdf);
            $data['preview_pdf'] = $this->uploadFile($request->file('preview_pdf'), 'uploads/books/previews');
        }
        if ($request->hasFile('full_pdf')) {
            $this->deleteOldFile($book->full_pdf);
            $data['full_pdf'] = $this->uploadFile($request->file('full_pdf'), 'uploads/books/full');
        }

        $book->update($data);
        $book->schoolClasses()->sync($request->school_class_ids);

        return redirect()->route('book.index')->with('success', 'Book updated successfully!');
    }

    /**
     * ডাটা ডিলিট
     */
    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        $this->deleteOldFile($book->image);
        $this->deleteOldFile($book->preview_pdf);
        $this->deleteOldFile($book->full_pdf);
        $book->delete();

        return redirect()->route('book.index')->with('success', 'Book deleted successfully!');
    }

    /**
     * Helper: ফাইল আপলোড
     */
    private function uploadFile($file, $path)
    {
        $name = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $file->move(public_path($path), $name);
        return $path . '/' . $name;
    }

    /**
     * Helper: পুরাতন ফাইল ডিলিট
     */
    private function deleteOldFile($filePath)
    {
        if ($filePath && File::exists(public_path($filePath))) {
            File::delete(public_path($filePath));
        }
    }
}