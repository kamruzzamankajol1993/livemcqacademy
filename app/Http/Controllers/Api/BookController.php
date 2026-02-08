<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookPayment;
use Illuminate\Http\Request;
use DB;
class BookController extends Controller
{


    /**
     * সকল বইয়ের লিস্ট এবং ফিল্টারিং
     * ফিল্টার করার নিয়ম: /api/books?class_id=1&subject_id=2
     */
    public function index(Request $request)
    {
        $query = Book::with(['category', 'subject', 'schoolClasses'])
                 ->withCount('reviews') 
                 ->where('status', 1);

        // ক্লাস ওয়াইজ ফিল্টার (Many-to-Many রিলেশন ব্যবহার করে)
        if ($request->class_id) {
            $query->whereHas('schoolClasses', function ($q) use ($request) {
                $q->where('school_class_id', $request->class_id);
            });
        }

        // সাবজেক্ট ওয়াইজ ফিল্টার
        if ($request->subject_id) {
            $query->where('subject_id', $request->subject_id);
        }
$books = $query->latest()->paginate(15);

    // প্রতিটি বইয়ের জন্য এভারেজ রেটিং অ্যাপেন্ড করা
    $books->getCollection()->transform(function ($book) {
        $book->average_rating = $book->average_rating; // মডেলের অ্যাট্রিবিউট থেকে আসবে
        return $book;
    });
        return response()->json([
        'status' => 'success',
        'data' => $books
    ]);
    }

    /**
     * বইয়ের ডিটেইলস এবং এক্সেস কন্ট্রোল
     * ইউআরএল: /api/book_detail?id=2
     */
   /**
     * আপনার দেওয়া মূল শো ফাংশন (অপরিবর্তিত)
     */
    public function show(Request $request)
    {
        $id = $request->id;

        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'ID parameter is required'], 400);
        }

        $user = auth()->user();
        $book = Book::with(['category', 'subject', 'schoolClasses'])->find($id);

        if (!$book) {
            return response()->json(['status' => 'error', 'message' => 'Book not found'], 404);
        }

        $activeSub = $user->activeSubscription;

        // চেক ১: ইউজার কি বইটি এককভাবে কিনেছে?
        $hasPurchased = DB::table('book_payments')
            ->where('user_id', $user->id)
            ->where('book_id', $id)
            ->where('status', 'completed')
            ->exists();

        $can_access = false;
        $message = "এই বইটি এক্সেস করতে পেমেন্ট করুন অথবা প্যাকেজ সাবস্ক্রাইব করুন।";

        // এক্সেস লজিক
        if ($book->price <= 0 || $hasPurchased) {
            $can_access = true;
            $message = "আপনি বইটি পড়তে পারবেন।";
        } elseif ($activeSub) {
            // সাবস্ক্রিপশন লিমিট চেক
            $limit = $activeSub->remaining_book_limit;
            if (strtolower($limit) == 'unlimited' || (int)$limit > 0) {
                $can_access = true;
                $message = "আপনার প্যাকেজ অনুযায়ী এক্সেস আছে।";
            } else {
                $message = "আপনার প্যাকেজের বই ডাউনলোডের লিমিট শেষ।";
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'book_details' => $book,
                'rating_info' => [
                'average' => $book->average_rating, // Book মডেলে ডিফাইন করা অ্যাট্রিবিউট
                'total_reviews' => $book->reviews()->count()
            ],
                'access_control' => [
                    'can_view_or_download' => $can_access,
                    'must_pay' => !$can_access,
                    'price' => $book->price,
                    'pdf_url' => $can_access ? asset($book->file_path) : null,
                    'remaining_package_limit' => $activeSub ? $activeSub->remaining_book_limit : 0
                ],
                'message' => $message
            ]
        ]);
    }

    /**
     * নতুন ফাংশন: ডাউনলোড বা ভিউ সফল হলে লিমিট কমানো
     * URL: /api/book_download_track?id=2
     */
    public function trackDownload(Request $request)
    {
        $id = $request->id;
        $user = auth()->user();

        if (!$id) {
            return response()->json(['status' => 'error', 'message' => 'Book ID required'], 400);
        }

        $activeSub = $user->activeSubscription;
        $book = Book::find($id);

        if (!$book) {
            return response()->json(['status' => 'error', 'message' => 'Book not found'], 404);
        }

        // লজিক: যদি বইটি ফ্রি না হয় এবং ইউজার সাবস্ক্রিপশন ব্যবহার করে এক্সেস করে, তবে লিমিট কমবে
        if (!$book->is_free && $activeSub) {
            $limit = $activeSub->remaining_book_limit;

            if (strtolower($limit) !== 'unlimited' && (int)$limit > 0) {
                $activeSub->remaining_book_limit = (int)$limit - 1;
                $activeSub->save();
            }
        }

        // ডাউনলোড রেকর্ড সেভ করা
        DB::table('book_downloads')->insert([
            'user_id' => $user->id,
            'book_id' => $id,
            'created_at' => now()
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Download tracked and limit updated.',
            'remaining_limit' => $activeSub ? $activeSub->remaining_book_limit : null
        ]);
    }

    /**
     * বই কেনার জন্য পেমেন্ট রিকোয়েস্ট
     */
    public function payForBook(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:books,id',
            'payment_method' => 'required',
            'amount' => 'required|numeric',
            'transaction_id' => 'required'
        ]);

        $payment = BookPayment::create([
            'user_id' => auth()->id(),
            'book_id' => $request->id,
            'payment_method' => $request->payment_method,
            'amount' => $request->amount,
            'transaction_id' => $request->transaction_id,
            'status' => 'pending' // অ্যাডমিন এটি ম্যানুয়ালি চেক করে 'completed' করবে
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'পেমেন্ট রিকোয়েস্ট জমা হয়েছে। অ্যাডমিন ভেরিফাই করার পর বইটি পড়তে পারবেন।'
        ]);
    }

 

    /**
 * ক্লাস এবং সাবজেক্ট অনুযায়ী প্রশ্নের তালিকা
 * URL: /api/book_questions?class_id=1&subject_id=5
 */
public function getQuestionsByFilter(Request $request)
{
    $request->validate([
        'class_id'   => 'required|exists:school_classes,id',
        'subject_id' => 'required|exists:subjects,id',
    ]);

    $classId = $request->class_id;
    $subjectId = $request->subject_id;

    // McqQuestion টেবিল থেকে ফিল্টার করা হচ্ছে
    $questions = \App\Models\McqQuestion::where('class_id', $classId)
        ->where('subject_id', $subjectId)
        ->where('status', 1)
        ->latest()
        ->paginate(20);

    if ($questions->isEmpty()) {
        return response()->json([
            'status'  => 'error',
            'message' => 'এই ক্লাস এবং সাবজেক্টের কোনো প্রশ্ন পাওয়া যায়নি।'
        ], 404);
    }

    return response()->json([
        'status' => 'success',
        'data'   => $questions
    ]);
}

/**
 * বইয়ের জন্য রিভিউ এবং রেটিং সাবমিট করা
 * URL: /api/book_review_submit
 */
public function submitReview(Request $request)
{
    $request->validate([
        'book_id' => 'required|exists:books,id',
        'rating'  => 'required|integer|min:1|max:5',
        'comment' => 'nullable|string|max:500',
    ]);

    $user = auth()->user();
    $bookId = $request->book_id;

    // ১. চেক করা হচ্ছে ইউজার বইটি পড়ার যোগ্য কি না
    if (!$this->checkBookAccess($user, $bookId)) {
        return response()->json([
            'status' => 'error', 
            'message' => 'রিভিউ দেওয়ার আগে বইটি পড়া বা কেনা আবশ্যক।'
        ], 403);
    }

    // ২. আপডেট অথবা নতুন রিভিউ তৈরি (এক ইউজার এক বইয়ে একবারই রিভিউ দিতে পারবে)
    $review = \App\Models\BookReview::updateOrCreate(
        ['user_id' => $user->id, 'book_id' => $bookId],
        [
            'rating' => $request->rating, 
            'comment' => $request->comment,
            'status' => 1 // ডিফল্টভাবে পাবলিশড
        ]
    );

    return response()->json([
        'status' => 'success',
        'message' => 'আপনার রিভিউ সফলভাবে জমা হয়েছে।',
        'data' => $review
    ]);
}

/**
 * ইউজার কি বইটি এক্সেস করার যোগ্য? (Helper Function)
 */
private function checkBookAccess($user, $bookId)
{
    $book = \App\Models\Book::find($bookId);
    if (!$book) return false;

    // ক. বই যদি ফ্রি হয়
    if ($book->price <= 0) {
        return true;
    }

    // খ. ইউজার কি বইটি এককভাবে কিনেছে?
    $hasPurchased = \DB::table('book_payments')
        ->where('user_id', $user->id)
        ->where('book_id', $bookId)
        ->where('status', 'completed')
        ->exists();
    if ($hasPurchased) return true;

    // গ. ইউজারের একটিভ প্যাকেজ সাবস্ক্রিপশন আছে কি না
    $activeSub = $user->activeSubscription;
    if ($activeSub) {
        $limit = $activeSub->remaining_book_limit;
        if (strtolower($limit) == 'unlimited' || (int)$limit > 0) {
            return true;
        }
    }

    return false;
}

/**
 * নির্দিষ্ট বইয়ের সকল রিভিউ দেখা
 * URL: /api/book_reviews?book_id=2
 */
public function getReviews(Request $request)
{
    $bookId = $request->id; // আপনার কুয়েরি প্যারামিটার অনুযায়ী

    $reviews = \App\Models\BookReview::with('user:id,name,image')
        ->where('book_id', $bookId)
        ->where('status', 1)
        ->latest()
        ->paginate(10);

    return response()->json([
        'status' => 'success',
        'data' => $reviews
    ]);
}
}