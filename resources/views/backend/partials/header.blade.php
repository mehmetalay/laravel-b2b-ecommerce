<div class="header-container fixed-top">
    <header class="header navbar navbar-expand-sm">
        <ul class="navbar-item theme-brand flex-row text-center">
            <li class="nav-item theme-logo">
                <a href="{{ route('admin.index') }}">
                    <img src="{{ image_url(config('images.default.logo'), 'images') }}" class="navbar-logo" alt="logo">
                </a>
            </li>
        </ul>
        <ul class="navbar-item flex-row ml-sm-auto">
            @yield('backend_header_right')
        </ul>
    </header>
</div>
