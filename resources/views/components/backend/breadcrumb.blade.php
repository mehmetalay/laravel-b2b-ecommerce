<div class="sub-header-container">
    <header class="header navbar navbar-expand-sm">
        <a href="javascript:;" class="sidebarCollapse" data-placement="bottom">
            <i class="las la-bars"></i>
        </a>

        <ul class="navbar-nav flex-row">
            <li>
                <div class="page-header">
                    <nav class="breadcrumb-one" aria-label="breadcrumb">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.index') }}">Anasayfa</a>
                            </li>

                            @isset($items)
                                @foreach ($items as $breadcrumb)
                                    @if ($loop->last)
                                        <li class="breadcrumb-item active" aria-current="page">
                                            {{ __($breadcrumb['label']) }}
                                        </li>
                                    @else
                                        <li class="breadcrumb-item">
                                            <a href="{{ $breadcrumb['url'] }}">{{ __($breadcrumb['label']) }}</a>
                                        </li>
                                    @endif
                                @endforeach
                            @endisset
                        </ol>
                    </nav>
                </div>
            </li>
        </ul>

        <ul class="navbar-nav d-flex align-center ml-auto">
            {{ $slot }}
        </ul>
    </header>
</div>
