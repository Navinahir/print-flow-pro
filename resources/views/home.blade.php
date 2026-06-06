@extends('marketing.layouts.marketing')

@section('title', config('printflow.brand.name').' — '.__('marketing.brand.page_title_suffix'))

@section('content')
    @include('marketing.components.hero')
    @include('marketing.components.features')
    @include('marketing.components.faq')
@endsection
