@php
    use App\Support\Merchant\PrintingNavigation;
    $printingNavItems = PrintingNavigation::items(request()->route()?->getName());
@endphp

@if (count($printingNavItems) > 0)
    <div class="space-y-1">
        @foreach ($printingNavItems as $item)
            @php
                /** @var \App\Enums\PrintingModule $module */
                $module = $item['module'];
            @endphp
            <a
                href="{{ route($module->routeName()) }}"
                class="merchant-sidebar-link {{ $item['active'] ? 'merchant-sidebar-link-active' : '' }}"
                data-sidebar-tooltip="{{ $item['label'] }}"
            >
                <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                <span class="merchant-sidebar-link__label">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </div>
@else
    <p class="merchant-sidebar__section-title normal-case tracking-normal">
        {{ __('merchant.printing.nav_none_enabled') }}
    </p>
@endif
