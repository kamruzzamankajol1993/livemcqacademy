@php
     $usr = Auth::user();
@endphp

<nav id="sidebar">
    <div class="sidebar-header">
        <img src="{{asset('/')}}{{$front_logo_name}}" alt="{{ $ins_name }} Logo" class="img-fluid">
    </div>
    <ul class="nav flex-column" id="sidebar-menu">
        @if ($usr->can('dashboardView'))
        <li class="nav-item">
            <a class="nav-link {{ Route::is('home') ? 'active' : '' }}" href="{{route('home')}}">
                <i data-feather="grid"></i>
                <span>Dashboard</span>
            </a>
        </li>
        @endif

        @if ($usr->can('aboutUsAdd') || $usr->can('aboutUsView') || $usr->can('aboutUsDelete') || $usr->can('aboutUsUpdate') || $usr->can('messageAdd') || $usr->can('messageView') || $usr->can('messageDelete') || $usr->can('messageUpdate') || $usr->can('extraPageAdd') || $usr->can('extraPageView') || $usr->can('extraPageDelete') || $usr->can('extraPageUpdate') || $usr->can('socialLinkAdd') || $usr->can('socialLinkView') || $usr->can('socialLinkDelete') || $usr->can('socialLinkUpdate') || $usr->can('reviewAdd') || $usr->can('reviewView') || $usr->can('reviewDelete') || $usr->can('reviewUpdate'))
        <li class="sidebar-title">
            <span>App Content</span>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="#cmsSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="cmsSubmenu">
                <i data-feather="file-text"></i>
                <span>Content</span>
                <i data-feather="chevron-down" class="ms-auto"></i>
            </a>
            <ul class="collapse list-unstyled {{ Route::is('footer-banner.index') || Route::is('hero-right-slider.*') || Route::is('hero-left-slider.index') || Route::is('hero-left-slider.create') || Route::is('hero-left-slider.edit') || Route::is('review.index') || Route::is('review.edit') || Route::is('review.show') || Route::is('extraPage.index') || Route::is('message.index') || Route::is('aboutUs.index') || Route::is('socialLink.index') || Route::is('settings.analytics.index') || Route::is('homepage-section.index') || Route::is('featured-category.index') || Route::is('highlight-product.index') || Route::is('offer-section.control.index') || Route::is('slider.control.index') || Route::is('sidebar-menu.control.index') || Route::is('frontend.control.index') ? 'show' : '' }}" id="cmsSubmenu" data-bs-parent="#sidebar-menu">

                @if ($usr->can('reviewAdd') || $usr->can('reviewView') || $usr->can('reviewDelete') || $usr->can('reviewUpdate'))
                <li>
                    <a class="nav-link {{ Route::is('review.index') || Route::is('review.edit') || Route::is('review.show') ? 'active' : '' }}" href="{{ route('review.index') }}">Review</a>
                </li>
                @endif

                @if ($usr->can('socialLinkAdd') || $usr->can('socialLinkView') || $usr->can('socialLinkDelete') || $usr->can('socialLinkUpdate'))
                <li>
                    <a class="nav-link {{ Route::is('socialLink.index') || Route::is('socialLink.edit') || Route::is('socialLink.create') ? 'active' : '' }}" href="{{ route('socialLink.index') }}">Social Link</a>
                </li>
                @endif

                @if ($usr->can('extraPageAdd') || $usr->can('extraPageView') || $usr->can('extraPageDelete') || $usr->can('extraPageUpdate'))
                <li>
                    <a class="nav-link {{ Route::is('extraPage.index') || Route::is('extraPage.edit') || Route::is('extraPage.create') ? 'active' : '' }}" href="{{ route('extraPage.index') }}">Extra Page</a>
                </li>
                @endif

                @if ($usr->can('messageAdd') || $usr->can('messageView') || $usr->can('messageDelete') || $usr->can('messageUpdate'))
                <li>
                    <a class="nav-link {{ Route::is('message.index') || Route::is('message.edit') || Route::is('message.create') ? 'active' : '' }}" href="{{ route('message.index') }}">Message</a>
                </li>
                @endif

                @if ($usr->can('aboutUsAdd') || $usr->can('aboutUsView') || $usr->can('aboutUsDelete') || $usr->can('aboutUsUpdate'))
                <li>
                    <a class="nav-link {{ Route::is('aboutUs.index') || Route::is('aboutUs.edit') || Route::is('aboutUs.create') ? 'active' : '' }}" href="{{ route('aboutUs.index') }}">About Us</a>
                </li>
                @endif
            </ul>
        </li>
        @endif

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
                <li>
                    <a class="nav-link {{ Route::is('designation.index') || Route::is('designation.edit') || Route::is('designation.create') ? 'active' : '' }}" href="{{ route('designation.index') }}">Designation</a>
                </li>
                @endif

                @if ($usr->can('userAdd') || $usr->can('userView') || $usr->can('userDelete') || $usr->can('userUpdate'))
                <li>
                    <a class="nav-link {{ Route::is('users.show') || Route::is('users.index') || Route::is('users.edit') || Route::is('users.create') ? 'active' : '' }}" href="{{ route('users.index') }}">User</a>
                </li>
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
                <li>
                    <a class="nav-link {{ Route::is('systemInformation.index') || Route::is('systemInformation.edit') || Route::is('systemInformation.create') ? 'active' : '' }}" href="{{ route('systemInformation.index') }}">Panel Settings</a>
                </li>
                @endif

                @if ($usr->can('roleAdd') || $usr->can('roleView') || $usr->can('roleEdit') || $usr->can('roleDelete'))
                <li>
                    <a class="nav-link {{ Route::is('roles.show') || Route::is('roles.index') || Route::is('roles.edit') || Route::is('roles.create') ? 'active' : '' }}" href="{{ route('roles.index') }}">Role Management</a>
                </li>
                @endif

                @if ($usr->can('permissionAdd') || $usr->can('permissionView') || $usr->can('permissionDelete') || $usr->can('permissionUpdate'))
                <li>
                    <a class="nav-link {{ Route::is('permissions.index') || Route::is('permissions.edit') || Route::is('permissions.create') ? 'active' : '' }}" href="{{ route('permissions.index') }}">Permission Management</a>
                </li>
                @endif

            </ul>
        </li>
        @endif
    </ul>
</nav>