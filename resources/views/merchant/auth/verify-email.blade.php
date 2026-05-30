@extends('merchant.layouts.app')

@section('title', __('auth.verify_email.title'))

@section('page-header')
    @include('merchant.components.page-header', [
        'title' => __('auth.verify_email.title'),
        'subtitle' => __('auth.verify_email.subtitle'),
    ])
@endsection

@section('content')
    <div class="merchant-card max-w-2xl">
        @if (session('status') === 'verification-link-sent')
            <p class="mb-4 text-sm font-medium text-emerald-700">
                {{ __('auth.verify_email.link_sent') }}
            </p>
        @endif

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="merchant-btn-primary">
                    {{ __('auth.verify_email.resend') }}
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="merchant-btn-secondary">
                    {{ __('auth.verify_email.logout') }}
                </button>
            </form>
        </div>
    </div>
@endsection
