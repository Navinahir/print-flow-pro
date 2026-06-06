@php

    $sizeClass = match ($size) {

        'xs' => 'merchant-user-avatar--xs',

        'sm' => 'merchant-user-avatar--sm',

        'md' => 'merchant-user-avatar--md',

        'lg' => 'merchant-user-avatar--lg',

        'xl' => 'merchant-user-avatar--xl',

        'sidebar' => 'merchant-user-avatar--sidebar',

        default => 'merchant-user-avatar--md',

    };

@endphp



<span

    {{ $attributes->class(['merchant-user-avatar', $sizeClass]) }}

    @if ($photoUrl) data-has-photo="true" @endif

    @if ($photoUrl) x-data="{ photoFailed: false }" @endif

>

    @if ($photoUrl)

        <img

            src="{{ $photoUrl }}"

            alt=""

            class="merchant-user-avatar__image"

            loading="lazy"

            x-show="! photoFailed"

            x-on:error="photoFailed = true"

        />



        <span

            class="merchant-user-avatar__initials"

            x-show="photoFailed"

            x-cloak

            aria-hidden="true"

        >{{ $initials }}</span>

    @else

        <span class="merchant-user-avatar__initials" aria-hidden="true">{{ $initials }}</span>

    @endif

</span>

