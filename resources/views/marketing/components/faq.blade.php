<!-- FAQ Section -->
<section class="pt-10 pb-10 md:pt-20 md:pb-20 bg-surface-container-low" id="faq">
    <div class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop">
        <div class="text-start mb-8 lg:mb-16 max-w-3xl">
        <h2
            class="text-[28px] md:text-[32px] lg:text-[42px] font-extrabold text-on-background mb-2"
        >
            {{ __('marketing.faq.title') }}
        </h2>
        </div>
        <div class="space-y-4 max-w-3xl">
        @foreach (__('marketing.faq.items') as $item)
        <details
            name="faq"
            class="group bg-surface-bright border border-outline-variant rounded-xl overflow-hidden [&_summary::-webkit-details-marker]:hidden"
        >
            <summary
            class="flex items-center justify-between p-6 cursor-pointer list-none transition-colors hover:bg-surface-container"
            >
            <span class="text-[18px] font-semibold font-label-md text-on-background text-start pe-4">
                {{ $item['question'] }}
            </span>
            <span
                class="material-symbols-outlined transition-transform group-open:rotate-180"
                aria-hidden="true"
            >
                expand_more
            </span>
            </summary>

            <div
            class="px-6 pb-6 text-body-md text-secondary text-start border-t border-outline-variant/30 pt-4"
            >
            {{ $item['answer'] }}
            </div>
        </details>
        @endforeach
        </div>
    </div>
</section>
