@props([
    'items' => [],
])

<nav aria-label="{{ __('merchant.breadcrumb.aria_label') }}" class="mb-4">
    <ol class="flex flex-wrap items-center gap-2 text-sm text-slate-500">
        <li>
            <a href="{{ route('dashboard') }}" class="hover:text-amber-700">
                {{ __('merchant.breadcrumb.home') }}
            </a>
        </li>
        @foreach ($items as $item)
            <li class="flex items-center gap-2">
                <span aria-hidden="true">/</span>
                @if (! empty($item['url']) && ! ($item['active'] ?? false))
                    <a href="{{ $item['url'] }}" class="hover:text-amber-700">
                        {{ $item['label'] }}
                    </a>
                @else
                    <span class="font-medium text-slate-800" @if ($item['active'] ?? false) aria-current="page" @endif>
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
