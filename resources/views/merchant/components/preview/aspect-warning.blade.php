<div
    {{ $attributes->class(['merchant-preview-aspect-warning']) }}
    data-preview-aspect-warning
    x-show="aspectWarningVisible"
    x-cloak
    role="alert"
    aria-live="assertive"
>
    <div class="merchant-preview-aspect-warning__content">
        <div class="merchant-preview-aspect-warning__icon" aria-hidden="true">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <div class="merchant-preview-aspect-warning__text">
            <p class="merchant-preview-aspect-warning__title">
                {{ __('merchant.preview.aspect_ratio.banner_title') }}
            </p>
            <p
                class="merchant-preview-aspect-warning__message"
                x-text="aspectValidation?.message ?? @js(__('merchant.preview.aspect_ratio.banner_message'))"
            ></p>
        </div>
    </div>

    <label class="merchant-preview-aspect-warning__force-toggle">
        <input
            type="checkbox"
            class="merchant-preview-aspect-warning__force-checkbox"
            x-model="forceAdjustment"
            x-on:change="updateAspectWarningVisibility()"
        />
        <span>{{ __('merchant.preview.aspect_ratio.force_adjustment') }}</span>
    </label>
</div>
