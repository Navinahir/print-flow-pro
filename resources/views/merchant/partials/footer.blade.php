<footer class="merchant-layout__footer border-t border-slate-200 bg-white px-4 py-6 dark:border-slate-700 dark:bg-slate-900 sm:px-6 lg:px-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-xs text-slate-500 dark:text-slate-400">
            {{ __('merchant.footer.copyright', ['year' => date('Y'), 'brand' => \App\Support\MerchantConfig::get('brand.name', __('merchant.brand.name'))]) }}
        </p>
        <div class="flex flex-wrap gap-4 text-xs text-slate-500 dark:text-slate-400">
            <span>{{ __('merchant.footer.help') }}</span>
            <span>{{ __('merchant.footer.privacy') }}</span>
            <span>{{ __('merchant.footer.terms') }}</span>
        </div>
    </div>
</footer>
