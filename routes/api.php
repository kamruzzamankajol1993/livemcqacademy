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
// Public Routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/forgot-password/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/forgot-password/reset', [AuthController::class, 'resetPassword']);

// Protected Routes (Login required)
Route::middleware('auth:sanctum')->group(function () {

// MCQ List with Pagination & Filters
Route::get('/mcqs', [McqQuestionController::class, 'index']);

// Single MCQ Details (Optional)
Route::get('/mcqs/{id}', [McqQuestionController::class, 'show']);
// 1. Academic Year List
Route::get('/academic-years', [AcademicYearController::class, 'index']);

// 2. Board List
Route::get('/boards', [BoardController::class, 'index']);

// 1. All Institute List
Route::get('/institutes', [InstituteController::class, 'index']);

// 2. Type Wise Institute List
// এখানে {type} ডাইনামিক হবে (যেমন: school, college, university ইত্যাদি)
Route::get('/institutes/type/{type}', [InstituteController::class, 'getByType']);
    
    // Dashboard / User Profile
    Route::get('/dashboard', [AuthController::class, 'dashboard']);
    
    // Logout
    Route::post('/logout', [AuthController::class, 'logout']);


    // ১. প্রোফাইল আপডেট রিকোয়েস্ট (ডাটা সহ এখানে হিট করবে)
    Route::post('/profile/update-request', [AuthController::class, 'updateProfileRequest']);

    // ২. যদি OTP লাগে, তখন OTP সহ এখানে হিট করবে
    Route::post('/profile/confirm-update', [AuthController::class, 'confirmProfileUpdate']);

    // 1. All Category List
Route::get('/categories', [CategoryController::class, 'index']);

// 2. Feature Wise Category List (By Feature ID)
// উদাহরণ: /api/categories/feature/1
Route::get('/categories/feature/{id}', [CategoryController::class, 'getCategoriesByFeature']);

Route::get('/features', [FeatureController::class, 'index']);

Route::get('/classes', [ClassController::class, 'index']);

// 2. Category Wise Class List (By Category ID)
// উদাহরণ: /api/classes/category/1
Route::get('/classes/category/{id}', [ClassController::class, 'getClassesByCategory']);

// 1. All Department List
Route::get('/departments', [DepartmentController::class, 'index']);

// 2. Class Wise Department List (By Class ID)
// উদাহরণ: /api/departments/class/1
Route::get('/departments/class/{id}', [DepartmentController::class, 'getDepartmentsByClass']);


// 1. All Subject List
Route::get('/subjects', [SubjectController::class, 'index']);

// 2. Class Wise Subject List (By Class ID)
// Example: /api/subjects/class/1
Route::get('/subjects/filter', [SubjectController::class, 'filterSubjects']);

// 1. All Section List
Route::get('/sections', [SectionController::class, 'index']);

// 2. Filter Sections (By Class ID or Subject ID)
// Example: /api/sections/filter?class_id=1
Route::get('/sections/filter', [SectionController::class, 'filterSections']);

// 1. All Chapter List
Route::get('/chapters', [ChapterController::class, 'index']);

// 2. Filter Chapters (By Subject, Section, or Class)
// Example: /api/chapters/filter?subject_id=1
// Example: /api/chapters/filter?subject_id=1&section_id=2
Route::get('/chapters/filter', [ChapterController::class, 'filterChapters']);

// 1. All Topic List
Route::get('/topics', [TopicController::class, 'index']);

// 2. Filter Topics (By Chapter, Subject, or Class)
// Example: /api/topics/filter?chapter_id=1
Route::get('/topics/filter', [TopicController::class, 'filterTopics']);

    // Default User Route (Optional - keeping your existing one)
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
});