@props([

    'currentModule',

])



@php

    /** @var \App\Enums\PrintingModule $currentModule */

    use App\Support\Merchant\PrintingNavigation;

    $navItems = PrintingNavigation::items(request()->route()?->getName());

@endphp



@if (count($navItems) > 1)

    <div class="flex flex-wrap gap-2">

        @foreach ($navItems as $item)

            @php

                /** @var \App\Enums\PrintingModule $module */

                $module = $item['module'];

                $isActive = $module === $currentModule;

            @endphp

            <a

                href="{{ route($module->routeName()) }}"

                class="merchant-printing-module-switcher__tab {{ $isActive ? 'merchant-printing-module-switcher__tab--active' : 'merchant-printing-module-switcher__tab--inactive' }}"

                @if ($isActive) aria-current="page" @endif

            >

                @if ($isActive)

                    <svg class="merchant-printing-module-switcher__check" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">

                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>

                    </svg>

                @endif

                {{ $item['label'] }}

            </a>

        @endforeach

    </div>

@endif

