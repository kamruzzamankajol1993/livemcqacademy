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


Route::resource('customerPersonalTicket', CustomerPersonalController::class);

Route::controller(CustomerPersonalController::class)->group(function () {
    Route::get('/customerGeneralTicketPdf/{id}', 'customerGeneralTicketPdf')->name('customerGeneralTicketPdf');
Route::get('/customerPersonalTicketPdf/{id}', 'customerPersonalTicketPdf')->name('customerPersonalTicketPdf');
    Route::get('/customerPersonalTicket', 'customerPersonalTicket')->name('customerPersonalTicket');
});
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


    Route::get('ajax-customers', [CustomerController::class, 'data'])->name('ajax.customer.data');
    Route::resource('customer', CustomerController::class);

 


    Route::controller(ServiceController::class)->group(function () {
    
        Route::get('/service/export','exportServices')->name('service.export');
    });

   

    


    Route::controller(CustomerController::class)->group(function () {
Route::get('/customers/export','exportCustomers')->name('customer.export');
        Route::get('/customers/check-email','checkEmailUniqueness')->name('customers.checkEmail');

    Route::get('/downloadcustomerPdf','downloadcustomerPdf')->name('downloadcustomerPdf');
    Route::get('/downloadcustomerExcel','downloadcustomerExcel')->name('downloadcustomerExcel');
    Route::get('/ajax-table-customer/data','data')->name('ajax.customertable.data');


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