@if (auth('web')->check() && auth('web')->user()->role === 'salesman')
    @forelse ($items as $current_account)
        @php
            $get_current_account = $currentAccountService->currentAccount();
            $count = auth('web')->user()->role === 'salesman' && $get_current_account && $get_current_account->id == $current_account->id;
            $active = $count ? 'active' : '';
            $chosen = $count ? trans('translations.current_account.secildi') : trans('translations.current_account.sec')
        @endphp
        <li>
            <a href="javascript:;" class="{{ $active }}" @if(!$count) data-id="{{ $current_account->id }}" data-action="current-account-select" @endif>
                <h6>{{ $current_account->code . ' - ' . $current_account->name . ' - ' . $current_account->province . ($current_account->province && $current_account->district ? ' /' : '') . ($current_account->district ? ' ' . $current_account->district : '') }}</h6>
                <span>{{ $chosen }}</span>
            </a>
        </li>
    @empty
        <li>
            <a href="javascript:;"><h6>{{ trans('translations.current_account.bayi_bulunamadi') }}.</h6></a>
        </li>
    @endforelse
@elseif (auth('web')->check() && auth('web')->user()->role === 'dealer')
    @forelse ($items as $item)
        @php
            $count = auth('web')->user()->role === 'dealer' && session()->has('acting_subdealer_id') && session()->get('acting_subdealer_id') == $item->id;
            $active = $count ? 'active' : '';
            $chosen = $count ? trans('translations.current_account.secildi') : trans('translations.current_account.sec');
        @endphp
        <li>
            <a href="javascript:;" class="{{ $active }}" @if(!$count) data-id="{{ $item->id }}" data-action="current-account-select" @endif>
                <h6>{{ $item->name }}</h6>
                <span>{{ $chosen }}</span>
            </a>
        </li>
    @empty
        <li>
            <a href="javascript:;"><h6>{{ trans('translations.current_account.bayi_bulunamadi') }}.</h6></a>
        </li>
    @endforelse
@endif

{{ $items->appends(request()->except('page'))->links('pagination::bootstrap-5') }}
