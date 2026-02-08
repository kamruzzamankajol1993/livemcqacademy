@php
     $usr = Auth::user();
@endphp

<nav id="sidebar">
    <div class="sidebar-header">
        <img src="{{asset('/')}}{{$front_logo_name}}" alt="{{ $ins_name }} Logo" class="img-fluid">
    </div>
    
    <ul class="nav flex-column" id="sidebar-menu">
        {{-- DASHBOARD --}}
        @if ($usr->can('dashboardView'))
        <li class="nav-item">
            <a class="nav-link {{ Route::is('home') ? 'active' : '' }}" href="{{route('home')}}">
                <i data-feather="grid"></i>
                <span>Dashboard</span>
            </a>
        </li>
        @endif

        {{-- DYNAMIC FEATURE TITLES & MENUS --}}
        @if(isset($globalFeatures))
            @foreach($globalFeatures as $feature)

            @if ($usr->can('mcqView') || $usr->can('bookView'))
                
                {{-- 1. Feature Title --}}
                <li class="sidebar-title">
                    <span>{{ $feature->english_name ?? $feature->bangla_name }}</span>
                </li>
                @endif

          
               {{-- Condition for '1st-12th Grade' Feature --}}
@if($feature->slug == '1st-12th-grade' || $feature->english_name == '1st-12th Grade')
@if ($usr->can('mcqAdd') || $usr->can('mcqView') || $usr->can('mcqDelete') || $usr->can('mcqUpdate'))
<li class="nav-item">
    <a class="nav-link collapsed" href="#questionBankSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="questionBankSubmenu">
        <i data-feather="book-open"></i>
        <span>Question Bank</span>
        <i data-feather="chevron-down" class="ms-auto"></i>
    </a>
    
    <ul class="collapse list-unstyled {{ Route::is('mcq.*') || Route::is('board.questions.*') || Route::is('institute.questions.*') ? 'show' : '' }}" id="questionBankSubmenu" data-bs-parent="#sidebar-menu">
        
        {{-- MCQ Dropdown (Level 2) --}}
        <li>
            <a class="nav-link collapsed" href="#mcqSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="mcqSubmenu">
                <span>MCQ</span>
                <i data-feather="chevron-down" class="ms-auto" style="width: 15px; height: 15px;"></i>
            </a>
            {{-- MCQ Items (Level 3) --}}
            <ul class="collapse list-unstyled {{ Route::is('mcq.*') ? 'show' : '' }}" id="mcqSubmenu" data-bs-parent="#questionBankSubmenu">
               @if ($usr->can('mcqAdd'))
                <li>
                    <a class="nav-link {{ Route::is('mcq.create') ? 'active' : '' }}" href="{{ route('mcq.create') }}" style="padding-left: 3rem;">
                        <i class="fa fa-plus-circle me-2" style="font-size: 10px;"></i> Add New MCQ
                    </a>
                </li>
                @endif
                @if ($usr->can('mcqView') || $usr->can('mcqDelete') || $usr->can('mcqUpdate'))
                <li>
                    <a class="nav-link {{ Route::is('mcq.index') ? 'active' : '' }}" href="{{ route('mcq.index') }}" style="padding-left: 3rem;">
                        <i class="fa fa-list me-2" style="font-size: 10px;"></i> MCQ List
                    </a>
                </li>
                @endif
            </ul>
        </li>

        {{-- Board Question (Dynamic) --}}
        <li>
            <a class="nav-link {{ Route::is('board.questions.index') ? 'active' : '' }}" href="{{ route('board.questions.index') }}">
                <i class="fa fa-graduation-cap me-2" style="font-size: 10px;"></i> Board Question
            </a>
        </li>

        {{-- Institute Wise List (Dynamic) --}}
        <li>
            <a class="nav-link {{ Route::is('institute.questions.index') ? 'active' : '' }}" href="{{ route('institute.questions.index') }}">
                <i class="fa fa-university me-2" style="font-size: 10px;"></i> Institute Wise List
            </a>
        </li>
    </ul>
</li>
@endif
@elseif($feature->slug == 'book' || $feature->english_name == 'Book')

 @if ($usr->can('bookAdd') || $usr->can('bookView') || $usr->can('bookDelete') || $usr->can('bookUpdate') || $usr->can('bookCategoryAdd') || $usr->can('bookCategoryView') || $usr->can('bookCategoryDelete') || $usr->can('bookCategoryUpdate'))

    {{-- BOOK MANAGEMENT DROPDOWN --}}
    <li class="nav-item">
        <a class="nav-link collapsed" href="#bookManagementSubmenu" data-bs-toggle="collapse" role="button" 
           aria-expanded="{{ Route::is('book.*') || Route::is('book-category.*') ? 'true' : 'false' }}" aria-controls="bookManagementSubmenu">
            <i data-feather="book"></i>
            <span>Book Management</span>
            <i data-feather="chevron-down" class="ms-auto"></i>
        </a>
        
        <ul class="collapse list-unstyled {{ Route::is('book.*') || Route::is('book-category.*') ? 'show' : '' }}" id="bookManagementSubmenu" data-bs-parent="#sidebar-menu">
            

            @if($usr->can('bookCategoryAdd') || $usr->can('bookCategoryView') || $usr->can('bookCategoryDelete') || $usr->can('bookCategoryUpdate'))
            {{-- Book Category --}}
            <li>
                <a class="nav-link {{ Route::is('book-category.*') ? 'active' : '' }}" href="{{ route('book-category.index') }}">
                    <i class="fa fa-list-ul me-2" style="font-size: 10px;"></i> Book Category
                </a>
            </li>
            @endif
            @if($usr->can('bookView') || $usr->can('bookDelete') || $usr->can('bookUpdate'))

            {{-- Book List (Updated with Route) --}}
            <li>
                <a class="nav-link {{ Route::is('book.index') ? 'active' : '' }}" href="{{ route('book.index') }}">
                    <i class="fa fa-file-pdf me-2" style="font-size: 10px;"></i> Book/PDF List
                </a>
            </li>
            @endif

             @if($usr->can('bookAdd'))

            {{-- Add New Book --}}
            <li>
                <a class="nav-link {{ Route::is('book.create') ? 'active' : '' }}" href="{{ route('book.create') }}">
                    <i class="fa fa-plus-circle me-2" style="font-size: 10px;"></i> Add New Book
                </a>
            </li>
            @endif

        </ul>
    </li>
    @endif
@endif

            @endforeach
        @endif
       

        @if ($usr->can('examAdd') || $usr->can('examView') || $usr->can('examDelete') || $usr->can('examUpdate') || $usr->can('examCategoryAdd') || $usr->can('examCategoryView') || $usr->can('examCategoryDelete') || $usr->can('examCategoryUpdate') || $usr->can('examPackageAdd') || $usr->can('examPackageView') || $usr->can('examPackageDelete') || $usr->can('examPackageUpdate'))
{{-- EXAM MANAGEMENT --}}
<li class="sidebar-title">
    <span>Exam Management</span>
</li>

<li class="nav-item">
    <a class="nav-link collapsed" href="#examManagementSubmenu" data-bs-toggle="collapse" role="button" 
       aria-expanded="{{ Route::is('exam-category.*') || Route::is('exam-setup.*') || Route::is('exam-package.*') ? 'true' : 'false' }}" aria-controls="examManagementSubmenu">
        <i data-feather="edit-3"></i>
        <span>Exams</span>
        <i data-feather="chevron-down" class="ms-auto"></i>
    </a>
    
    <ul class="collapse list-unstyled {{ Route::is('exam-category.*') || Route::is('exam-setup.*') || Route::is('exam-package.*') ? 'show' : '' }}" id="examManagementSubmenu" data-bs-parent="#sidebar-menu">
        

        @if($usr->can('examCategoryAdd') || $usr->can('examCategoryView') || $usr->can('examCategoryDelete') || $usr->can('examCategoryUpdate'))
        {{-- Exam Category --}}
        <li>
            <a class="nav-link {{ Route::is('exam-category.*') ? 'active' : '' }}" href="{{ route('exam-category.index') }}">
                <i class="fa fa-list-alt me-2" style="font-size: 10px;"></i> Exam Category
            </a>
        </li>
        @endif


@if($usr->can('examAdd') || $usr->can('examView') || $usr->can('examDelete') || $usr->can('examUpdate'))
        {{-- Exam Setup (General) --}}
        <li>
            <a class="nav-link {{ Route::is('exam-setup.*') ? 'active' : '' }}" href="{{ route('exam-setup.index') }}">
                <i class="fa fa-cog me-2" style="font-size: 10px;"></i> Exam Setup
            </a>
        </li>

        {{-- Exam Package (New Module) --}}
        <li>
            <a class="nav-link {{ Route::is('exam-package.*') ? 'active' : '' }}" href="{{ route('exam-package.index') }}">
                <i class="fa fa-archive me-2" style="font-size: 10px;"></i> Exam Package
            </a>
        </li>
        @endif

    </ul>
</li>

@endif
 @if ($usr->can('studentView') || $usr->can('studentAdd') || $usr->can('studentUpdate') || $usr->can('studentDelete'))
 <li class="sidebar-title">
                    <span>Student Management</span>
        </li>
       
<li class="nav-item">
    <a class="nav-link {{ Route::is('student.*') ? 'active' : '' }}" href="{{ route('student.index') }}">
        <i data-feather="users"></i>
        <span>Student</span>
    </a>
</li>
@endif

@if ($usr->can('packageAdd') || $usr->can('packageView') || $usr->can('packageFeatureAdd') || $usr->can('packageFeatureView'))

        <li class="sidebar-title">
                    <span>Package And Feature</span>
        </li>

        {{-- PRICING & PACKAGES --}}
<li class="nav-item">
    <a class="nav-link collapsed" href="#packageSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="packageSubmenu">
        <i data-feather="package"></i>
        <span>Packages</span>
        <i data-feather="chevron-down" class="ms-auto"></i>
    </a>
    <ul class="collapse list-unstyled {{ Route::is('package.*') || Route::is('feature-list.*') ? 'show' : '' }}" id="packageSubmenu" data-bs-parent="#sidebar-menu">
        
        {{-- Feature List --}}
        @if($usr->can('packageFeatureAdd') || $usr->can('packageFeatureView'))
        <li>
            <a class="nav-link {{ Route::is('feature-list.index') ? 'active' : '' }}" href="{{ route('feature-list.index') }}">
              <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i>  Feature List
            </a>
        </li>
        @endif

        {{-- Packages --}}
        @if ($usr->can('packageView'))
        <li>
            <a class="nav-link {{ Route::is('package.index') ? 'active' : '' }}" href="{{ route('package.index') }}">
               <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> All Packages
            </a>
        </li>
        @endif
        {{-- Add New Package --}}
        @if ($usr->can('packageAdd'))
        <li>
            <a class="nav-link {{ Route::is('package.create') ? 'active' : '' }}" href="{{ route('package.create') }}">
               <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Add New Package
            </a>
        </li>
        @endif
    </ul>
</li>
@endif
        {{-- MASTER SETUP DROPDOWN --}}
        @if ($usr->can('featureView') || $usr->can('featureAdd') || 
             $usr->can('categoryView') || $usr->can('categoryAdd') || 
             $usr->can('classDepartmentView') || $usr->can('classDepartmentAdd') ||
             $usr->can('schoolClassView') || $usr->can('schoolClassAdd') ||
             $usr->can('instituteAdd') || $usr->can('instituteView') ||
             $usr->can('academicYearView') || $usr->can('academicYearAdd') ||
             $usr->can('boardView') || $usr->can('boardAdd') ||
             $usr->can('subjectView') || $usr->can('subjectAdd')) 
        
        <li class="nav-item">
            <a class="nav-link" href="#masterSetupSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="masterSetupSubmenu">
                <i data-feather="layers"></i>
                <span>Master Setup</span>
                <i data-feather="chevron-down" class="ms-auto"></i>
            </a>
            
            <ul class="collapse list-unstyled {{ Route::is('classDepartment.*') || Route::is('institute.*') || Route::is('academicYear.*') || Route::is('board.*') || Route::is('topic.*') || Route::is('chapter.*') || Route::is('section.*') || Route::is('subject.*') ||  Route::is('feature.*') || Route::is('category.*') || Route::is('schoolClass.*') ? 'show' : '' }}" id="masterSetupSubmenu" data-bs-parent="#sidebar-menu">

                {{-- BOARD --}}
                @if ($usr->can('boardAdd') || $usr->can('boardView') || $usr->can('boardDelete') || $usr->can('boardUpdate'))
                <li><a class="nav-link {{ Route::is('board.index') ? 'active' : '' }}" href="{{ route('board.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Board</a></li>
                @endif

                {{-- ACADEMIC YEAR --}}
                @if ($usr->can('academicYearAdd') || $usr->can('academicYearView') || $usr->can('academicYearDelete') || $usr->can('academicYearUpdate'))
                <li><a class="nav-link {{ Route::is('academicYear.index') ? 'active' : '' }}" href="{{ route('academicYear.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Academic Year</a></li>
                @endif

                {{-- INSTITUTE --}}
                @if ($usr->can('instituteAdd') || $usr->can('instituteView'))
                <li><a class="nav-link {{ Route::is('institute.index') ? 'active' : '' }}" href="{{ route('institute.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Institute</a></li>
                @endif

                {{-- FEATURE --}}
                @if ($usr->can('featureAdd') || $usr->can('featureView') || $usr->can('featureDelete') || $usr->can('featureUpdate'))
                <li><a class="nav-link {{ Route::is('feature.index') ? 'active' : '' }}" href="{{ route('feature.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Feature</a></li>
                @endif

                {{-- CATEGORY --}}
                @if ($usr->can('categoryAdd') || $usr->can('categoryView') || $usr->can('categoryDelete') || $usr->can('categoryUpdate'))
                <li><a class="nav-link {{ Route::is('category.index') ? 'active' : '' }}" href="{{ route('category.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Category</a></li>
                @endif 

                {{-- CLASS --}}
                @if ($usr->can('schoolClassAdd') || $usr->can('schoolClassView') || $usr->can('schoolClassDelete') || $usr->can('schoolClassUpdate'))
                <li><a class="nav-link {{ Route::is('schoolClass.index') ? 'active' : '' }}" href="{{ route('schoolClass.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Class</a></li>
                @endif

                {{-- CLASS DEPARTMENT --}}
                @if ($usr->can('classDepartmentAdd') || $usr->can('classDepartmentView') || $usr->can('classDepartmentDelete') || $usr->can('classDepartmentUpdate'))
                <li><a class="nav-link {{ Route::is('classDepartment.index') ? 'active' : '' }}" href="{{ route('classDepartment.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Class Department</a></li>
                @endif

                {{-- SUBJECT --}}
                @if ($usr->can('subjectAdd') || $usr->can('subjectView') || $usr->can('subjectDelete') || $usr->can('subjectUpdate'))
                <li><a class="nav-link {{ Route::is('subject.index') ? 'active' : '' }}" href="{{ route('subject.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Subject</a></li>
                @endif

                {{-- SECTION --}}
                @if ($usr->can('sectionAdd') || $usr->can('sectionView') || $usr->can('sectionDelete') || $usr->can('sectionUpdate'))
                <li><a class="nav-link {{ Route::is('section.index') ? 'active' : '' }}" href="{{ route('section.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Section</a></li>
                @endif

                {{-- CHAPTER --}}
                @if ($usr->can('chapterAdd') || $usr->can('chapterView') || $usr->can('chapterDelete') || $usr->can('chapterUpdate'))
                <li><a class="nav-link {{ Route::is('chapter.index') ? 'active' : '' }}" href="{{ route('chapter.index') }}"> 
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Chapter</a></li>
                @endif

                {{-- TOPIC --}}
                @if ($usr->can('topicAdd') || $usr->can('topicView') || $usr->can('topicDelete') || $usr->can('topicUpdate'))
                <li><a class="nav-link {{ Route::is('topic.index') ? 'active' : '' }}" href="{{ route('topic.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Topic</a></li>
                @endif

            </ul>
        </li>
        @endif

        {{-- EXTRA CONTENT --}}
        @if ($usr->can('aboutUsAdd') || $usr->can('aboutUsView') || $usr->can('aboutUsDelete') || $usr->can('aboutUsUpdate') || $usr->can('messageAdd') || $usr->can('messageView') || $usr->can('messageDelete') || $usr->can('messageUpdate') || $usr->can('extraPageAdd') || $usr->can('extraPageView') || $usr->can('extraPageDelete') || $usr->can('extraPageUpdate') || $usr->can('socialLinkAdd') || $usr->can('socialLinkView') || $usr->can('socialLinkDelete') || $usr->can('socialLinkUpdate') || $usr->can('reviewAdd') || $usr->can('reviewView') || $usr->can('reviewDelete') || $usr->can('reviewUpdate'))
        <li class="sidebar-title">
            <span>Extra Content</span>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#cmsSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="cmsSubmenu">
                <i data-feather="file-text"></i>
                <span>Content</span>
                <i data-feather="chevron-down" class="ms-auto"></i>
            </a>
            <ul class="collapse list-unstyled {{ Route::is('footer-banner.index') || Route::is('hero-right-slider.*') || Route::is('hero-left-slider.index') || Route::is('hero-left-slider.create') || Route::is('hero-left-slider.edit') || Route::is('review.index') || Route::is('review.edit') || Route::is('review.show') || Route::is('extraPage.index') || Route::is('message.index') || Route::is('aboutUs.index') || Route::is('socialLink.index') || Route::is('settings.analytics.index') || Route::is('homepage-section.index') || Route::is('featured-category.index') || Route::is('highlight-product.index') || Route::is('offer-section.control.index') || Route::is('slider.control.index') || Route::is('sidebar-menu.control.index') || Route::is('frontend.control.index') ? 'show' : '' }}" id="cmsSubmenu" data-bs-parent="#sidebar-menu">

                @if ($usr->can('reviewAdd') || $usr->can('reviewView') || $usr->can('reviewDelete') || $usr->can('reviewUpdate'))
                <li><a class="nav-link {{ Route::is('review.index') || Route::is('review.edit') || Route::is('review.show') ? 'active' : '' }}" href="{{ route('review.index') }}">Review</a></li>
                @endif

                @if ($usr->can('socialLinkAdd') || $usr->can('socialLinkView') || $usr->can('socialLinkDelete') || $usr->can('socialLinkUpdate'))
                <li><a class="nav-link {{ Route::is('socialLink.index') || Route::is('socialLink.edit') || Route::is('socialLink.create') ? 'active' : '' }}" href="{{ route('socialLink.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Social Link</a></li>
                @endif

                @if ($usr->can('extraPageAdd') || $usr->can('extraPageView') || $usr->can('extraPageDelete') || $usr->can('extraPageUpdate'))
                <li><a class="nav-link {{ Route::is('extraPage.index') || Route::is('extraPage.edit') || Route::is('extraPage.create') ? 'active' : '' }}" href="{{ route('extraPage.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Extra Page</a></li>
                @endif

                @if ($usr->can('messageAdd') || $usr->can('messageView') || $usr->can('messageDelete') || $usr->can('messageUpdate'))
                <li><a class="nav-link {{ Route::is('message.index') || Route::is('message.edit') || Route::is('message.create') ? 'active' : '' }}" href="{{ route('message.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Message</a></li>
                @endif

                @if ($usr->can('aboutUsAdd') || $usr->can('aboutUsView') || $usr->can('aboutUsDelete') || $usr->can('aboutUsUpdate'))
                <li><a class="nav-link {{ Route::is('aboutUs.index') || Route::is('aboutUs.edit') || Route::is('aboutUs.create') ? 'active' : '' }}" href="{{ route('aboutUs.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> About Us</a></li>
                @endif
            </ul>
        </li>
        @endif

        {{-- SETTINGS --}}
        @if ($usr->can('userAdd') || $usr->can('userView') || $usr->can('userDelete') || $usr->can('userUpdate') || $usr->can('designationAdd') || $usr->can('designationView') || $usr->can('designationDelete') || $usr->can('designationUpdate') || $usr->can('branchAdd') || $usr->can('branchView') || $usr->can('branchDelete') || $usr->can('branchUpdate'))
        <li class="sidebar-title">
            <span>Settings</span>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="#accountSettingsSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="accountSettingsSubmenu">
                <i data-feather="user"></i>
                <span>Account</span>
                <i data-feather="chevron-down" class="ms-auto"></i>
            </a>
            <ul class="collapse list-unstyled {{ Route::is('users.show') || Route::is('users.index') || Route::is('users.edit') || Route::is('users.create') || Route::is('branch.index') || Route::is('branch.edit') || Route::is('branch.create') || Route::is('designation.index') || Route::is('designation.edit') || Route::is('designation.create') ? 'show' : '' }}" id="accountSettingsSubmenu" data-bs-parent="#sidebar-menu">

                @if ($usr->can('designationAdd') || $usr->can('designationView') || $usr->can('designationDelete') || $usr->can('designationUpdate'))
                <li><a class="nav-link {{ Route::is('designation.index') || Route::is('designation.edit') || Route::is('designation.create') ? 'active' : '' }}" href="{{ route('designation.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Designation</a></li>
                @endif

                @if ($usr->can('userAdd') || $usr->can('userView') || $usr->can('userDelete') || $usr->can('userUpdate'))
                <li><a class="nav-link {{ Route::is('users.show') || Route::is('users.index') || Route::is('users.edit') || Route::is('users.create') ? 'active' : '' }}" href="{{ route('users.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> User</a></li>
                @endif

            </ul>
        </li>
        @endif

        @if ($usr->can('permissionAdd') || $usr->can('permissionView') || $usr->can('permissionDelete') || $usr->can('permissionUpdate') || $usr->can('roleAdd') || $usr->can('roleView') || $usr->can('roleUpdate') || $usr->can('roleDelete') || $usr->can('panelSettingAdd') || $usr->can('panelSettingView') || $usr->can('panelSettingDelete') || $usr->can('panelSettingUpdate'))
        <li class="nav-item mb-5">
            <a class="nav-link" href="#generalSettingsSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="generalSettingsSubmenu">
                <i data-feather="settings"></i>
                <span>General</span>
                <i data-feather="chevron-down" class="ms-auto"></i>
            </a>
            <ul class="collapse list-unstyled {{ Route::is('roles.show') || Route::is('permissions.index') || Route::is('permissions.edit') || Route::is('permissions.create') || Route::is('roles.index') || Route::is('roles.edit') || Route::is('roles.create') || Route::is('systemInformation.index') || Route::is('systemInformation.edit') || Route::is('systemInformation.create') ? 'show' : '' }}" id="generalSettingsSubmenu" data-bs-parent="#sidebar-menu">

                @if ($usr->can('panelSettingAdd') || $usr->can('panelSettingView') || $usr->can('panelSettingDelete') || $usr->can('panelSettingUpdate'))
                <li><a class="nav-link {{ Route::is('systemInformation.index') || Route::is('systemInformation.edit') || Route::is('systemInformation.create') ? 'active' : '' }}" href="{{ route('systemInformation.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Panel Settings</a></li>
                @endif

                @if ($usr->can('roleAdd') || $usr->can('roleView') || $usr->can('roleEdit') || $usr->can('roleDelete'))
                <li><a class="nav-link {{ Route::is('roles.show') || Route::is('roles.index') || Route::is('roles.edit') || Route::is('roles.create') ? 'active' : '' }}" href="{{ route('roles.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Role Management</a></li>
                @endif

                @if ($usr->can('permissionAdd') || $usr->can('permissionView') || $usr->can('permissionDelete') || $usr->can('permissionUpdate'))
                <li><a class="nav-link {{ Route::is('permissions.index') || Route::is('permissions.edit') || Route::is('permissions.create') ? 'active' : '' }}" href="{{ route('permissions.index') }}">
                    <i class="fa fa-arrow-right me-2" style="font-size:10px;"></i> Permission Management</a></li>
                @endif

            </ul>
        </li>
        @endif
    </ul>
</nav>