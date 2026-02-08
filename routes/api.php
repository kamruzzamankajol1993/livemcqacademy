<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FeatureController;
use App\Http\Controllers\Api\ClassController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\SubjectController;
use App\Http\Controllers\Api\SectionController;
use App\Http\Controllers\Api\ChapterController;
use App\Http\Controllers\Api\TopicController;
use App\Http\Controllers\Api\InstituteController;
use App\Http\Controllers\Api\AcademicYearController;
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\McqQuestionController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\PackageController;
use App\Http\Controllers\Api\SubscriptionController;
use App\Http\Controllers\Api\ExamController;
use App\Http\Controllers\Api\SelfTestController;
use App\Http\Controllers\Api\BookController;



// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/login/facebook', [SocialAuthController::class, 'loginWithFacebook']);
Route::post('/login/google', [SocialAuthController::class, 'loginWithGoogle']);
Route::post('/forgot-password/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword']);

// Protected Routes (Login required)
Route::middleware('auth:sanctum')->group(function () {


// start book routes
Route::get('/book_questions', [BookController::class, 'getQuestionsByFilter']);
Route::post('/book_review_submit', [BookController::class, 'submitReview']);
Route::get('/book_reviews', [BookController::class, 'getReviews']);
Route::get('/books', [BookController::class, 'index']); 
Route::get('/book_detail', [BookController::class, 'show']); 
Route::post('/book_purchase', [BookController::class, 'payForBook']); 
 Route::post('/book_download_track', [BookController::class, 'trackDownload']);
// end book routes


// Self Test Routes
Route::get('/assessment_config', [SelfTestController::class, 'getConfig']);
    Route::post('/assessment_start', [SelfTestController::class, 'startSelfTest']);
    Route::post('/assessment_submit', [SelfTestController::class, 'submitResult']);
    Route::get('/assessment_history', [SelfTestController::class, 'userHistory']);
    Route::get('/assessment_leaderboard', [SelfTestController::class, 'selfTestLeaderboard']);
    Route::get('/assessment_review', [SelfTestController::class, 'reviewSelfTest']);
// end Self Test Routes

// start exam routes
Route::get('/exam_history', [ExamController::class, 'examHistory']);
Route::get('/leaderboard', [ExamController::class, 'leaderboard']);
Route::get('/exam_questions', [ExamController::class, 'getQuestions']);
Route::get('/all_exam', [ExamController::class, 'index']); // সকল এক্সাম
Route::get('/class_wise_exam', [ExamController::class, 'classWise']); // ?class_id=1
Route::get('/department_wise_exam', [ExamController::class, 'departmentWise']); // ?department_id=1
Route::get('/subject_wise_exam', [ExamController::class, 'subjectWise']);
Route::get('/exam_detail', [ExamController::class, 'show']);
Route::post('/submit_exam', [ExamController::class, 'submitExam']);
// end exam routes



// Subscription & Package Routes

Route::get('/my-subscriptions', [SubscriptionController::class, 'subscriptionHistory']);
Route::get('/my-payments', [SubscriptionController::class, 'paymentHistory']);

Route::get('/payment-methods', [SubscriptionController::class, 'paymentMethods']);
Route::post('/purchase-package', [SubscriptionController::class, 'purchasePackage']);

Route::get('/packages', [PackageController::class, 'index']);
Route::get('/package_detail', [PackageController::class, 'show']);

// end Subscription & Package Routes

// MCQ List with Pagination & Filters
Route::get('/mcqs', [McqQuestionController::class, 'index']);
Route::get('/mcqs_detail', [McqQuestionController::class, 'show']);
Route::get('/academic-years', [AcademicYearController::class, 'index']);
Route::get('/boards', [BoardController::class, 'index']);
Route::get('/institutes', [InstituteController::class, 'index']);
Route::get('/institutes_type', [InstituteController::class, 'getByType']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories_feature', [CategoryController::class, 'getCategoriesByFeature']);
Route::get('/all_features', [FeatureController::class, 'index']);
Route::get('/classes', [ClassController::class, 'index']);
Route::get('/classes_category', [ClassController::class, 'getClassesByCategory']);
Route::get('/departments', [DepartmentController::class, 'index']);  
Route::get('/departments_class', [DepartmentController::class, 'getDepartmentsByClass']);
Route::get('/subjects', [SubjectController::class, 'index']);
Route::get('/subjects_filter', [SubjectController::class, 'filterSubjects']);
Route::get('/sections', [SectionController::class, 'index']);
Route::get('/sections_filter', [SectionController::class, 'filterSections']);
Route::get('/chapters', [ChapterController::class, 'index']);
Route::get('/chapters_filter', [ChapterController::class, 'filterChapters']);
Route::get('/topics', [TopicController::class, 'index']);
Route::get('/topics_filter', [TopicController::class, 'filterTopics']);

//end MCQ List with Pagination & Filters

// Dashboard / User Profile
Route::get('/dashboard', [AuthController::class, 'dashboard']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/profile/update-request', [AuthController::class, 'updateProfileRequest']);
Route::post('/profile/confirm-update', [AuthController::class, 'confirmProfileUpdate']);
// end Dashboard / User Profile

    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});