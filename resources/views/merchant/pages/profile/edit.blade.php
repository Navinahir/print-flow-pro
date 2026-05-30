@extends('merchant.layouts.app')

@section('title', __('merchant.profile.title'))

@section('breadcrumbs')
    @include('merchant.components.breadcrumb', [
        'items' => [
            ['label' => __('merchant.profile.title'), 'active' => true],
        ],
    ])
@endsection

@section('page-header')
    @include('merchant.components.page-header', [
        'title' => __('merchant.profile.title'),
        'subtitle' => __('merchant.profile.subtitle'),
    ])
@endsection

@section('content')
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="merchant-card">
            @include('merchant.pages.profile.partials.update-profile-photo-form')
        </div>

        <div class="merchant-card">
            @include('merchant.pages.profile.partials.update-profile-information-form')
        </div>

        <div class="merchant-card">
            @include('merchant.pages.profile.partials.update-password-form')
        </div>

        <div class="merchant-card">
            @include('merchant.pages.profile.partials.delete-user-form')
        </div>
    </div>
@endsection
