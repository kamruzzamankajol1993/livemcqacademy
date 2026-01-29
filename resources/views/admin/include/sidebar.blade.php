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
                
                {{-- 1. Feature Title --}}
                <li class="sidebar-title">
                    <span>{{ $feature->english_name ?? $feature->bangla_name }}</span>
                </li>

                {{-- 2. Feature Specific Menus --}}
                
                {{-- Condition for '1st-12th Grade' Feature --}}
                @if($feature->slug == '1st-12th-grade' || $feature->english_name == '1st-12th Grade')
                <li class="nav-item">
                    <a class="nav-link collapsed" href="#questionBankSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="questionBankSubmenu">
                        <i data-feather="book-open"></i>
                        <span>Question Bank</span>
                        <i data-feather="chevron-down" class="ms-auto"></i>
                    </a>
                    
                    <ul class="collapse list-unstyled {{ Route::is('mcq.*') ? 'show' : '' }}" id="questionBankSubmenu" data-bs-parent="#sidebar-menu">
                        
                        {{-- MCQ Dropdown (Level 2) --}}
                        <li>
                            <a class="nav-link collapsed" href="#mcqSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="mcqSubmenu">
                                <span>MCQ</span>
                                <i data-feather="chevron-down" class="ms-auto" style="width: 15px; height: 15px;"></i>
                            </a>
                            {{-- MCQ Items (Level 3) --}}
                            <ul class="collapse list-unstyled {{ Route::is('mcq.*') ? 'show' : '' }}" id="mcqSubmenu" data-bs-parent="#questionBankSubmenu">
                                <li>
                                    <a class="nav-link {{ Route::is('mcq.create') ? 'active' : '' }}" href="{{ route('mcq.create') }}" style="padding-left: 3rem;">
                                        <i class="fa fa-plus-circle me-2" style="font-size: 10px;"></i> Add New MCQ
                                    </a>
                                </li>
                                <li>
                                    <a class="nav-link {{ Route::is('mcq.index') ? 'active' : '' }}" href="{{ route('mcq.index') }}" style="padding-left: 3rem;">
                                        <i class="fa fa-list me-2" style="font-size: 10px;"></i> MCQ List
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- Other Question Types --}}
                        {{-- <li>
                            <a class="nav-link" href="#">CQ</a>
                        </li> --}}
                        <li>
                            <a class="nav-link" href="#">Board Question</a>
                        </li>
                    </ul>
                </li>
                @endif

            @endforeach
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
                <li><a class="nav-link {{ Route::is('board.index') ? 'active' : '' }}" href="{{ route('board.index') }}">Board</a></li>
                @endif

                {{-- ACADEMIC YEAR --}}
                @if ($usr->can('academicYearAdd') || $usr->can('academicYearView') || $usr->can('academicYearDelete') || $usr->can('academicYearUpdate'))
                <li><a class="nav-link {{ Route::is('academicYear.index') ? 'active' : '' }}" href="{{ route('academicYear.index') }}">Academic Year</a></li>
                @endif

                {{-- INSTITUTE --}}
                @if ($usr->can('instituteAdd') || $usr->can('instituteView'))
                <li><a class="nav-link {{ Route::is('institute.index') ? 'active' : '' }}" href="{{ route('institute.index') }}">Institute</a></li>
                @endif

                {{-- FEATURE --}}
                @if ($usr->can('featureAdd') || $usr->can('featureView') || $usr->can('featureDelete') || $usr->can('featureUpdate'))
                <li><a class="nav-link {{ Route::is('feature.index') ? 'active' : '' }}" href="{{ route('feature.index') }}">Feature</a></li>
                @endif

                {{-- CATEGORY --}}
                @if ($usr->can('categoryAdd') || $usr->can('categoryView') || $usr->can('categoryDelete') || $usr->can('categoryUpdate'))
                <li><a class="nav-link {{ Route::is('category.index') ? 'active' : '' }}" href="{{ route('category.index') }}">Category</a></li>
                @endif 

                {{-- CLASS --}}
                @if ($usr->can('schoolClassAdd') || $usr->can('schoolClassView') || $usr->can('schoolClassDelete') || $usr->can('schoolClassUpdate'))
                <li><a class="nav-link {{ Route::is('schoolClass.index') ? 'active' : '' }}" href="{{ route('schoolClass.index') }}">Class</a></li>
                @endif

                {{-- CLASS DEPARTMENT --}}
                @if ($usr->can('classDepartmentAdd') || $usr->can('classDepartmentView') || $usr->can('classDepartmentDelete') || $usr->can('classDepartmentUpdate'))
                <li><a class="nav-link {{ Route::is('classDepartment.index') ? 'active' : '' }}" href="{{ route('classDepartment.index') }}">Class Department</a></li>
                @endif

                {{-- SUBJECT --}}
                @if ($usr->can('subjectAdd') || $usr->can('subjectView') || $usr->can('subjectDelete') || $usr->can('subjectUpdate'))
                <li><a class="nav-link {{ Route::is('subject.index') ? 'active' : '' }}" href="{{ route('subject.index') }}">Subject</a></li>
                @endif

                {{-- SECTION --}}
                @if ($usr->can('sectionAdd') || $usr->can('sectionView') || $usr->can('sectionDelete') || $usr->can('sectionUpdate'))
                <li><a class="nav-link {{ Route::is('section.index') ? 'active' : '' }}" href="{{ route('section.index') }}">Section</a></li>
                @endif

                {{-- CHAPTER --}}
                @if ($usr->can('chapterAdd') || $usr->can('chapterView') || $usr->can('chapterDelete') || $usr->can('chapterUpdate'))
                <li><a class="nav-link {{ Route::is('chapter.index') ? 'active' : '' }}" href="{{ route('chapter.index') }}">Chapter</a></li>
                @endif

                {{-- TOPIC --}}
                @if ($usr->can('topicAdd') || $usr->can('topicView') || $usr->can('topicDelete') || $usr->can('topicUpdate'))
                <li><a class="nav-link {{ Route::is('topic.index') ? 'active' : '' }}" href="{{ route('topic.index') }}">Topic</a></li>
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
                <li><a class="nav-link {{ Route::is('socialLink.index') || Route::is('socialLink.edit') || Route::is('socialLink.create') ? 'active' : '' }}" href="{{ route('socialLink.index') }}">Social Link</a></li>
                @endif

                @if ($usr->can('extraPageAdd') || $usr->can('extraPageView') || $usr->can('extraPageDelete') || $usr->can('extraPageUpdate'))
                <li><a class="nav-link {{ Route::is('extraPage.index') || Route::is('extraPage.edit') || Route::is('extraPage.create') ? 'active' : '' }}" href="{{ route('extraPage.index') }}">Extra Page</a></li>
                @endif

                @if ($usr->can('messageAdd') || $usr->can('messageView') || $usr->can('messageDelete') || $usr->can('messageUpdate'))
                <li><a class="nav-link {{ Route::is('message.index') || Route::is('message.edit') || Route::is('message.create') ? 'active' : '' }}" href="{{ route('message.index') }}">Message</a></li>
                @endif

                @if ($usr->can('aboutUsAdd') || $usr->can('aboutUsView') || $usr->can('aboutUsDelete') || $usr->can('aboutUsUpdate'))
                <li><a class="nav-link {{ Route::is('aboutUs.index') || Route::is('aboutUs.edit') || Route::is('aboutUs.create') ? 'active' : '' }}" href="{{ route('aboutUs.index') }}">About Us</a></li>
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
                <li><a class="nav-link {{ Route::is('designation.index') || Route::is('designation.edit') || Route::is('designation.create') ? 'active' : '' }}" href="{{ route('designation.index') }}">Designation</a></li>
                @endif

                @if ($usr->can('userAdd') || $usr->can('userView') || $usr->can('userDelete') || $usr->can('userUpdate'))
                <li><a class="nav-link {{ Route::is('users.show') || Route::is('users.index') || Route::is('users.edit') || Route::is('users.create') ? 'active' : '' }}" href="{{ route('users.index') }}">User</a></li>
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
                <li><a class="nav-link {{ Route::is('systemInformation.index') || Route::is('systemInformation.edit') || Route::is('systemInformation.create') ? 'active' : '' }}" href="{{ route('systemInformation.index') }}">Panel Settings</a></li>
                @endif

                @if ($usr->can('roleAdd') || $usr->can('roleView') || $usr->can('roleEdit') || $usr->can('roleDelete'))
                <li><a class="nav-link {{ Route::is('roles.show') || Route::is('roles.index') || Route::is('roles.edit') || Route::is('roles.create') ? 'active' : '' }}" href="{{ route('roles.index') }}">Role Management</a></li>
                @endif

                @if ($usr->can('permissionAdd') || $usr->can('permissionView') || $usr->can('permissionDelete') || $usr->can('permissionUpdate'))
                <li><a class="nav-link {{ Route::is('permissions.index') || Route::is('permissions.edit') || Route::is('permissions.create') ? 'active' : '' }}" href="{{ route('permissions.index') }}">Permission Management</a></li>
                @endif

            </ul>
        </li>
        @endif
    </ul>
</nav>