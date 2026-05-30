@extends('merchant.layouts.app')

@section('vite')
    @vite(['resources/css/merchant-delivery-labels.css', 'resources/js/merchant-delivery-labels.js'])
@endsection

@section('title', $workspace->title)

@section('breadcrumbs')
    @include('merchant.components.breadcrumb', [
        'items' => [
            ['label' => __('merchant.nav.printing'), 'url' => null],
            ['label' => $workspace->title, 'active' => true],
        ],
    ])
@endsection

@section('page-header')
    @component('merchant.components.page-header', [
        'title' => $workspace->title,
        'subtitle' => $workspace->subtitle,
    ])
        @slot('actions')
            @include('merchant.printing.components.module-switcher', [
                'currentModule' => $workspace->module,
            ])
        @endslot
    @endcomponent
@endsection

@section('content')
    @include('merchant.printing.delivery-labels.components.workspace-shell', [
        'workspace' => $workspace,
    ])
@endsection
