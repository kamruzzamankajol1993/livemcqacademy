<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\Auth\LoginController;
use App\Http\Controllers\Admin\SystemInformationController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\PermissionController;
use App\Http\Controllers\Admin\DesignationController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\SocialLinkController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\SubjectController;
use App\Http\Controllers\Admin\ExtraPageController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\AboutUsController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\BookCategoryController;
use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Front\TextController;
use App\Http\Controllers\Front\AuthController;
use App\Http\Controllers\Front\CustomerPersonalController;
use App\Http\Controllers\Admin\DefaultLocationController;
use App\Http\Controllers\Admin\SearchLogController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\SchoolClassController;
use App\Http\Controllers\Admin\FeatureController;
use App\Http\Controllers\Admin\FrontendControlController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SectionController;
use App\Http\Controllers\Admin\ChapterController;
use App\Http\Controllers\Admin\TopicController;
use App\Http\Controllers\Admin\BoardController;
use App\Http\Controllers\Admin\AcademicYearController;
use App\Http\Controllers\Admin\InstituteController;
use App\Http\Controllers\Admin\ClassDepartmentController;
use App\Http\Controllers\Admin\McqQuestionController;
use App\Http\Controllers\Admin\FeatureListController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\ExamCategoryController;
use App\Http\Controllers\Admin\ExamController;
use App\Http\Controllers\Admin\ExamPackageController;
Route::get('/clear', function() {
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('config:cache');
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    return redirect()->back();
});



Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');



Route::controller(LoginController::class)->group(function () {

    Route::get('/', 'viewLoginPage')->name('viewLoginPage');
    Route::get('/password/reset', 'showLinkRequestForm')->name('showLinkRequestForm');
    Route::post('/password/reset/submit', 'reset')->name('reset');

});

Route::controller(TextController::class)->group(function () {
    Route::post('/textMessageAll', 'textMessage')->name('text.index');
});

Route::controller(AuthController::class)->group(function () {
    Route::get('/login-register', 'loginregisterPage')->name('front.loginRegister');

    Route::post('/login-user-post', 'loginUserPost')->name('front.loginUserPost');
    Route::post('/register-user-post', 'registerUserPost')->name('front.registerUserPost');

      // --- NEW PASSWORD RESET ROUTES ---
    Route::get('forgot-password', 'showForgotPasswordForm')->name('front.password.request');
    Route::post('forgot-password', 'sendResetLink')->name('front.password.email');
    Route::get('reset-password/{token}', 'showResetPasswordForm')->name('front.password.reset');
    Route::post('reset-password', 'resetPassword')->name('front.password.update'); // Note: This reuses the standard 'password.update' name
});


    



Route::group(['middleware' => ['auth']], function() {


Route::resource('book', BookController::class);

    // ২. AJAX Data Fetch Route (ইনডেক্স টেবিলের ডাটা প্যাগিনেশন ও সার্চের জন্য)
    Route::get('get-books-data', [BookController::class, 'fetchData'])->name('book.fetch');


Route::get('get-subjects-by-class', [BookController::class, 'getSubjectsByClass'])->name('book.getSubjects');

// ১. Book Category Resource Route (index, store, update, destroy মেথড এর জন্য)
    Route::resource('book-category', BookCategoryController::class);

    // ২. AJAX Data Fetch Route (টেবিল প্যাগিনেশন এবং সার্চের জন্য)
    Route::get('get-book-category-data', [BookCategoryController::class, 'fetchData'])->name('book-category.fetch');

    // ৩. Drag & Drop Reorder Route (সিরিয়াল আপডেট করার জন্য)
    Route::post('book-category-reorder', [BookCategoryController::class, 'reorder'])->name('book-category.reorder');

// Exam Package Resource Route
    Route::resource('exam-package', ExamPackageController::class);

    // AJAX Dependency Routes
    Route::get('exam-package/get-departments/{class_id}', [ExamPackageController::class, 'getDepartments'])->name('exam-package.get-departments');
    Route::get('exam-package/fetch/subjects', [ExamPackageController::class, 'getSubjects'])->name('exam-package.get-subjects');
    Route::get('exam-package/fetch/chapters', [ExamPackageController::class, 'getChapters'])->name('exam-package.get-chapters');
    Route::get('exam-package/fetch/topics', [ExamPackageController::class, 'getTopics'])->name('exam-package.get-topics');
// ড্র্যাগ অ্যান্ড ড্রপ সিরিয়াল আপডেটের জন্য কাস্টম রাউট (এটি রিসোর্স রাউটের উপরে রাখা ভালো)
    Route::post('exam-category-reorder', [ExamCategoryController::class, 'reorder'])->name('exam.category.serial');
    
    // এক্সাম ক্যাটাগরির জন্য স্ট্যান্ডার্ড রিসোর্স রাউট (Index, Store, Edit, Update, Destroy)
    Route::resource('exam-category', ExamCategoryController::class);
Route::resource('exam-setup', ExamController::class);

Route::post('student/{id}/assign-package', [CustomerController::class, 'assignPackage'])->name('student.assignPackage');
// Institute Question Routes
    Route::get('institute-questions', [McqQuestionController::class, 'instituteQuestionIndex'])->name('institute.questions.index');
    Route::get('ajax-institute-questions-list', [McqQuestionController::class, 'instituteQuestionData'])->name('institute.questions.ajax.data');

// --- Package Module ---
    // AJAX Data Route for Package Table
    Route::get('ajax_package_data', [PackageController::class, 'data'])->name('ajax.package.data');
    Route::resource('package', PackageController::class);


Route::get('ajax_feature_list', [FeatureListController::class, 'data'])->name('ajax.feature_list.data');
    Route::resource('feature-list', FeatureListController::class);


Route::get('ajax-mcq-institutes', [App\Http\Controllers\Admin\McqQuestionController::class, 'getInstitutesByType'])->name('mcq.ajax.institutes');
// Board Question Routes
    Route::get('board-questions', [McqQuestionController::class, 'boardQuestionIndex'])->name('board.questions.index');
    Route::get('ajax-board-questions-list', [McqQuestionController::class, 'boardQuestionData'])->name('board.questions.ajax.data');
Route::resource('mcq', McqQuestionController::class);
// MCQ AJAX Data Route
Route::get('ajax-mcq-list', [App\Http\Controllers\Admin\McqQuestionController::class, 'data'])->name('mcq.ajax.data');
Route::get('mcq-sample-download', [App\Http\Controllers\Admin\McqQuestionController::class, 'downloadSample'])->name('mcq.sample');
Route::post('mcq-import', [App\Http\Controllers\Admin\McqQuestionController::class, 'import'])->name('mcq.import');
// AJAX Routes for MCQ Module
Route::get('ajax-mcq-classes', [McqQuestionController::class, 'getClasses'])->name('mcq.ajax.classes');
Route::get('ajax-mcq-departments', [McqQuestionController::class, 'getDepartments'])->name('mcq.ajax.departments');
Route::get('ajax-mcq-subjects', [McqQuestionController::class, 'getSubjects'])->name('mcq.ajax.subjects');
Route::get('ajax-mcq-chapters', [McqQuestionController::class, 'getChapters'])->name('mcq.ajax.chapters');
Route::get('ajax-mcq-topics', [McqQuestionController::class, 'getTopics'])->name('mcq.ajax.topics');


Route::post('class-department-reorder', [ClassDepartmentController::class, 'reorder'])->name('classDepartment.reorder');
Route::get('ajax_class_department', [ClassDepartmentController::class, 'data'])->name('ajax.classDepartment.data');
Route::resource('classDepartment', ClassDepartmentController::class);


Route::get('institute-sample-download', [InstituteController::class, 'downloadSample'])->name('institute.sample');
Route::post('institute-import', [InstituteController::class, 'import'])->name('institute.import');
Route::post('institute-reorder', [InstituteController::class, 'reorder'])->name('institute.reorder');
Route::get('ajax_institute', [InstituteController::class, 'data'])->name('ajax.institute.data');
Route::resource('institute', InstituteController::class);


Route::get('academic-year-sample-download', [AcademicYearController::class, 'downloadSample'])->name('academicYear.sample');
Route::post('academic-year-import', [AcademicYearController::class, 'import'])->name('academicYear.import');
Route::post('academic-year-reorder', [AcademicYearController::class, 'reorder'])->name('academicYear.reorder');
Route::get('ajax_academic_year', [AcademicYearController::class, 'data'])->name('ajax.academicYear.data');
Route::resource('academicYear', AcademicYearController::class);


Route::get('board-sample-download', [BoardController::class, 'downloadSample'])->name('board.sample');
Route::post('board-import', [BoardController::class, 'import'])->name('board.import');
Route::post('board-reorder', [BoardController::class, 'reorder'])->name('board.reorder');
Route::get('ajax_board', [BoardController::class, 'data'])->name('ajax.board.data');
Route::resource('board', BoardController::class);
Route::get('/ajax-get-departments-by-classes', [App\Http\Controllers\Admin\SubjectController::class, 'getDepartmentsByClasses'])->name('ajax.get.departments');
Route::get('/ajax-get-chapters-by-class-subject', [App\Http\Controllers\Admin\TopicController::class, 'getChaptersByClassAndSubject'])->name('ajax.get.chapters');
// AJAX Routes for Dependent Dropdowns
Route::get('/ajax-get-classes-by-category', [App\Http\Controllers\Admin\SectionController::class, 'getClassesByCategory'])->name('ajax.get.classes');
Route::get('/ajax-get-subjects-by-class', [App\Http\Controllers\Admin\SectionController::class, 'getSubjectsByClass'])->name('ajax.get.subjects');
Route::get('/ajax-get-sections-by-class-subject', [App\Http\Controllers\Admin\ChapterController::class, 'getSectionsByClassAndSubject'])->name('ajax.get.sections');

Route::get('topic-sample-download', [TopicController::class, 'downloadSample'])->name('topic.sample');
    Route::post('topic-import', [TopicController::class, 'import'])->name('topic.import');
    Route::post('topic-reorder', [TopicController::class, 'reorder'])->name('topic.reorder');
    Route::get('ajax_topic', [TopicController::class, 'data'])->name('ajax.topic.data');
    Route::resource('topic', TopicController::class);


Route::get('chapter-sample-download', [ChapterController::class, 'downloadSample'])->name('chapter.sample');
    Route::post('chapter-import', [ChapterController::class, 'import'])->name('chapter.import');
    Route::post('chapter-reorder', [ChapterController::class, 'reorder'])->name('chapter.reorder');
    Route::get('ajax_chapter', [ChapterController::class, 'data'])->name('ajax.chapter.data');
    Route::resource('chapter', ChapterController::class);


Route::get('section-sample-download', [SectionController::class, 'downloadSample'])->name('section.sample');
    Route::post('section-import', [SectionController::class, 'import'])->name('section.import');
    Route::post('section-reorder', [SectionController::class, 'reorder'])->name('section.reorder');
    Route::get('ajax_section', [SectionController::class, 'data'])->name('ajax.section.data');
    Route::resource('section', SectionController::class);


Route::get('subject-sample-download', [SubjectController::class, 'downloadSample'])->name('subject.sample');
    Route::post('subject-import', [SubjectController::class, 'import'])->name('subject.import');
    Route::post('subject-reorder', [SubjectController::class, 'reorder'])->name('subject.reorder');
    Route::get('ajax_subject', [SubjectController::class, 'data'])->name('ajax.subject.data');
    Route::resource('subject', SubjectController::class);

Route::get('class_sample-download', [SchoolClassController::class, 'downloadSample'])->name('class.sample');
// Class Module Routes
    Route::post('class_reorder', [SchoolClassController::class, 'reorder'])->name('class.reorder');
    Route::post('class_import', [SchoolClassController::class, 'import'])->name('class.import');
    Route::get('ajax_class', [SchoolClassController::class, 'data'])->name('ajax.class.data');
    Route::resource('schoolClass', SchoolClassController::class); // Route name 'schoolClass.index' etc.

Route::post('feature_reorder', [FeatureController::class, 'reorder'])->name('feature.reorder');
// Ajax Data Route
    Route::get('ajax_feature', [FeatureController::class, 'data'])->name('ajax.feature.data');
Route::resource('feature', FeatureController::class);

  

Route::delete('review-images/{image}', [ReviewController::class, 'destroyImage'])->name('review.image.destroy');
   
    // Shareholder List Routes
    Route::get('/shareholders', [UserController::class, 'shareholderIndex'])->name('shareholders.index');
    Route::get('/ajax-shareholders-data', [UserController::class, 'shareholdersData'])->name('ajax.shareholders.data');


Route::post('category_reorder', [CategoryController::class, 'reorder'])->name('category.reorder'); // New
Route::resource('category', CategoryController::class);
Route::get('ajax_category', [CategoryController::class, 'data'])->name('ajax.category.data');


    Route::controller(AuthController::class)->group(function () {

        Route::get('/user-dashboard', 'userDashboard')->name('front.userDashboard');
        Route::post('/profile/update', 'updateProfile')->name('profile.update');
        Route::post('/password/update', 'updatePassword')->name('password.update');
});
    //website part




Route::resource('defaultLocation', DefaultLocationController::class);
    Route::resource('searchLog', SearchLogController::class);

     Route::controller(SearchLogController::class)->group(function () {

    Route::get('/ajax-table-searchLog/data','data')->name('ajax.searchLogtable.data');


    });

    Route::resource('aboutUs', AboutUsController::class);
    Route::resource('contact', ContactController::class);

    //Route::resource('review', ReviewController::class);
    // Review Routes
    Route::resource('review', ReviewController::class);
    Route::get('ajax/reviews/data', [ReviewController::class, 'data'])->name('ajax.review.data');
   
    Route::resource('socialLink', SocialLinkController::class);
    Route::resource('extraPage', ExtraPageController::class);
    Route::resource('message', MessageController::class);

    //setting part start
    Route::resource('setting', SettingController::class);
    Route::resource('branch', BranchController::class);
    Route::resource('designation', DesignationController::class);
    Route::resource('permissions', PermissionController::class);
    Route::resource('roles', RoleController::class);
    Route::resource('users', UserController::class);
    Route::resource('systemInformation', SystemInformationController::class);


    Route::get('ajax-students', [CustomerController::class, 'data'])->name('ajax.student.data');
    Route::resource('student', CustomerController::class);

 


    Route::controller(ServiceController::class)->group(function () {
    
        Route::get('/service/export','exportServices')->name('service.export');
    });

   

    


    Route::controller(CustomerController::class)->group(function () {
Route::get('/students/export','exportstudents')->name('student.export');
        Route::get('/students/check-email','checkEmailUniqueness')->name('students.checkEmail');

    Route::get('/downloadstudentPdf','downloadstudentPdf')->name('downloadstudentPdf');
    Route::get('/downloadstudentExcel','downloadstudentExcel')->name('downloadstudentExcel');
    Route::get('/ajax-table-student/data','data')->name('ajax.studenttable.data');


    });





    Route::controller(UserController::class)->group(function () {

    Route::get('/downloadUserPdf','downloadUserPdf')->name('downloadUserPdf');
    Route::get('/downloadUserExcel','downloadUserExcel')->name('downloadUserExcel');
    Route::get('/ajax-table-user/data','data')->name('ajax.usertable.data');


    });

  

    Route::controller(SystemInformationController::class)->group(function () {

    Route::get('/downloadSystemInformationPdf','downloadSystemInformationPdf')->name('downloadSystemInformationPdf');
    Route::get('/downloadSystemInformationExcel','downloadSystemInformationExcel')->name('downloadSystemInformationExcel');
    Route::get('/ajax-table-systemInformation/data','data')->name('ajax.systemInformationtable.data');


    });



    Route::controller(RoleController::class)->group(function () {

    Route::get('/downloadRolePdf','downloadRolePdf')->name('downloadRolePdf');
    Route::get('/downloadRoleExcel','downloadRoleExcel')->name('downloadRoleExcel');
    Route::get('/ajax-table-role/data','data')->name('ajax.roletable.data');


    });


     Route::controller(PermissionController::class)->group(function () {

    Route::get('/downloadPermissionPdf','downloadPermissionPdf')->name('downloadPermissionPdf');
    Route::get('/downloadPermissionExcel','downloadPermissionExcel')->name('downloadPermissionExcel');
    Route::get('/ajax-table-permission/data','data')->name('ajax.permissiontable.data');


    });


    Route::controller(BranchController::class)->group(function () {

    Route::get('/downloadBranchPdf','downloadBranchPdf')->name('downloadBranchPdf');
    Route::get('/downloadBranchExcel','downloadBranchExcel')->name('downloadBranchExcel');
    Route::get('/ajax-table-branch/data','data')->name('ajax.branchtable.data');


    });

    Route::controller(DesignationController::class)->group(function () {

    Route::get('/downloadDesignationPdf','downloadDesignationPdf')->name('downloadDesignationPdf');
    Route::get('/downloadDesignationExcel','downloadDesignationExcel')->name('downloadDesignationExcel');
    Route::get('/ajax-table-designation/data','data')->name('ajax.designationtable.data');
    

    });

    Route::controller(UserController::class)->group(function () {


        Route::get('/activeOrInActiveUser/{status}/{id}', 'activeOrInActiveUser')->name('activeOrInActiveUser');

    });


    Route::controller(SettingController::class)->group(function () {

        Route::get('/error_500', 'error_500')->name('error_500');
        Route::get('/profileView', 'profileView')->name('profileView');
        Route::get('/profileSetting', 'profileSetting')->name('profileSetting');

        Route::post('/profileSettingUpdate', 'profileSettingUpdate')->name('profileSettingUpdate');
        Route::post('/passwordUpdate', 'passwordUpdate')->name('passwordUpdate');

        Route::post('/checkMailPost', 'checkMailPost')->name('checkMailPost');
        Route::get('/checkMailForPassword', 'checkMailForPassword')->name('checkMailForPassword');

        Route::get('/newEmailNotify', 'newEmailNotify')->name('newEmailNotify');
        Route::post('/postPasswordChange', 'postPasswordChange')->name('postPasswordChange');
        Route::get('/accountPasswordChange/{id}', 'accountPasswordChange')->name('accountPasswordChange');




    });
    //setting part end
});