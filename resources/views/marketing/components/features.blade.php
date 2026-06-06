@php
    $cards = [
        [
            'icon' => 'print_connect',
            'key' => 'orders',
        ],
        [
            'icon' => 'label',
            'key' => 'labels',
        ],
        [
            'icon' => 'list_alt',
            'key' => 'picking',
        ],
    ];
@endphp

<!-- Feature Cards -->
<section class="pt-10 pb-10 md:pt-20 md:pb-20 bg-surface-container-lowest" id="features">
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-center mb-8 lg:mb-16">
        <h2 class="text-[28px] md:text-[32px] lg:text-[42px] font-extrabold text-on-background mb-2">
            {{ __('marketing.features.title') }}
        </h2>
        @if (filled(__('marketing.features.subtitle')))
            <p class="text-body-md text-secondary max-w-2xl mx-auto">
                {{ __('marketing.features.subtitle') }}
            </p>
        @endif
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-gutter">
        @foreach ($cards as $card)
        <div
            class="group bg-surface-bright border border-outline-variant p-8 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 hover:-translate-y-2"
        >
            <div
            class="w-16 h-16 bg-primary/10 rounded-lg flex items-center justify-center mb-6 group-hover:bg-primary transition-colors"
            >
            <span
                class="material-symbols-outlined text-primary group-hover:text-on-primary text-3xl"
                aria-hidden="true"
            >{{ $card['icon'] }}</span>
            </div>
            <h3 class="font-headline-md text-headline-md text-on-background mb-4">
            {{ __('marketing.features.cards.'.$card['key'].'.title') }}
            </h3>
            @if (filled(__('marketing.features.cards.'.$card['key'].'.description')))
                <p class="text-body-md text-secondary mb-6">
                    {{ __('marketing.features.cards.'.$card['key'].'.description') }}
                </p>
            @endif
            <ul class="space-y-3">
            @foreach (__('marketing.features.cards.'.$card['key'].'.bullets') as $bullet)
            <li class="flex items-center gap-2 text-body-md text-on-surface/80">
                <span class="material-symbols-outlined text-primary text-sm" aria-hidden="true">
                check
                </span>
                {{ $bullet }}
            </li>
            @endforeach
            </ul>
        </div>
        @endforeach
        </div>
    </div>
</section>
