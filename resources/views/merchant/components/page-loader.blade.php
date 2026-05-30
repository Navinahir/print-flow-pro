<div
    class="merchant-page-loader"
    role="status"
    aria-live="polite"
    aria-label="{{ __('merchant.components.page_loader.aria_label') }}"
>
    <div class="merchant-page-loader__content">
        <div class="merchant-spinner" aria-hidden="true"></div>
        <p class="merchant-page-loader__message merchant-page-loader__message--content mt-4 text-sm font-medium text-slate-600 dark:text-slate-300">
            {{ __('merchant.components.page_loader.content_message') }}
        </p>
        <p class="merchant-page-loader__message merchant-page-loader__message--locale mt-4 text-sm font-medium text-slate-600 dark:text-slate-300">
            {{ __('merchant.components.page_loader.message') }}
        </p>
    </div>
</div>
