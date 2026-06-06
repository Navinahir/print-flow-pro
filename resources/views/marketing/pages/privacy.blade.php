@extends('marketing.layouts.marketing')

@section('title', __('marketing.legal.privacy.title').' — '.__('marketing.brand.page_title_suffix'))

@section('content')
    <section class="w-full px-margin-mobile md:px-margin-desktop py-12 max-w-container-max mx-auto">
        <h1 class="text-3xl font-bold text-on-background mb-6">{{ __('marketing.legal.privacy.title') }}</h1>
        <div class="prose prose-slate max-w-3xl dark:prose-invert space-y-4">
            @foreach (__('marketing.legal.privacy.sections') as $section)
                <div>
                    <h2 class="text-xl font-semibold mb-2">{{ $section['heading'] }}</h2>
                    <p class="text-on-surface-variant">{{ $section['body'] }}</p>
                </div>
            @endforeach
        </div>
    </section>
@endsection
