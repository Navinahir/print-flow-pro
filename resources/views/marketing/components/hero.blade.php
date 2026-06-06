<!-- Hero Section -->
<section class="relative hero-gradient pt-10 pb-10 md:pt-20 md:pb-20 overflow-hidden">
    <div
        class="max-w-container-max mx-auto px-margin-mobile md:px-margin-desktop flex flex-col lg:flex-row gap-5 items-center"
    >
        <div
        class="flex flex-col items-center lg:items-start gap-4 z-10 w-[100%] lg:w-[45%]"
        >
        <div
            class="inline-flex text-center lg:text-start items-center px-4 py-1.5 rounded-full bg-primary/10 border border-primary/20 w-fit"
        >
            <span class="text-label-md font-label-md text-primary">
                {{ __('marketing.hero.badge') }}
            </span>
        </div>
        <h1 class="flex flex-col text-center lg:text-start">
            <span
            class="fw-bolder text-[28px] md:text-[34px] lg:text-[48px] font-extrabold text-on-background leading-none mb-2"
            >{{ __('marketing.hero.title_line_1') }}</span>
            <span
            class="text-[32px] md:text-[38px] lg:text-[56px] font-extrabold text-primary leading-none mt-1"
            >{{ __('marketing.hero.title_line_2') }}</span>
        </h1>
        <p
            class="font-medium text-center lg:text-start text-[16px] md:text-[18px] lg:text-[22px] text-on-surface/80 flex items-center gap-2"
        >
            <span
            class="material-symbols-outlined text-primary"
            style="font-variation-settings: &quot;FILL&quot; 1"
            aria-hidden="true"
            >check_circle</span>
            {{ __('marketing.hero.supports') }}
        </p>
        <p
            class="text-[16px] lg:text-[18px] text-center lg:text-start text-secondary max-w-xl"
        >
            {{ __('marketing.hero.description') }}
        </p>
        <div class="flex flex-wrap gap-4 mt-1">
            <a  
                href="{{ $merchantRegisterUrl }}"
                class="px-6 py-2 md:px-8 md:py-4 font-headline-md bg-primary text-on-primary rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 active:scale-95 flex items-center gap-4"
                >
                {{ __('marketing.hero.cta') }}
                <span class="material-symbols-outlined" aria-hidden="true">arrow_forward</span>
            </a>
        </div>
        </div>
        <div class="relative w-[100%] lg:w-[55%]">
        <div class="">
            <img
            alt="{{ __('marketing.hero.image_alt') }}"
            class="w-full h-full object-cover max-w-[650px] mx-auto"
            src="{{ asset(__('marketing.hero.banner_image')) }}"
            />
        </div>
        <div class="flex flex-wrap justify-center gap-3 mt-6">
            <span
            class="flex flex-row sm:flex-col justify-center w-full sm:w-auto items-center gap-2 px-5 py-4 rounded-full border border-outline-variant font-medium text-label-md text-on-surface bg-surface-container-low shadow-sm transition-transform hover:-translate-y-1"
            >
            <span class="material-symbols-outlined" aria-hidden="true">order_approve</span>
            {{ __('marketing.hero.pill_order') }}
            </span>
            <span
            class="flex flex-row sm:flex-col justify-center w-full sm:w-auto items-center gap-2 px-5 py-4 rounded-full border border-outline-variant font-medium text-label-md text-on-surface bg-surface-container-low shadow-sm transition-transform hover:-translate-y-1"
            >
            <span class="material-symbols-outlined" aria-hidden="true">package_2</span>
            {{ __('marketing.hero.pill_shipping') }}
            </span>
            <span
            class="flex flex-row sm:flex-col justify-center w-full sm:w-auto items-center gap-2 px-5 py-4 rounded-full border border-outline-variant font-medium text-label-md text-on-surface bg-surface-container-low shadow-sm transition-transform hover:-translate-y-1"
            >
            <span class="material-symbols-outlined" aria-hidden="true">finance</span>
            {{ __('marketing.hero.pill_pick_list') }}
            </span>
        </div>
        <div
            class="absolute z-[-1] left-[50%] top-[50%] -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-primary/10 rounded-full blur-3xl"
        ></div>
        </div>
    </div>
</section>
