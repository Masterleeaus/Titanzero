@php
    $pageTitle = $pageTitle ?? 'Titan Zero';
    $pageIcon  = $pageIcon  ?? 'ti ti-sparkles';

    use Illuminate\Support\Facades\Route;

    $coachCards = [
        ['title' => __('Business Coach'), 'desc' => __('Pricing, quoting, ops systems, growth, hiring, cashflow.'), 'route' => 'titan.zero.business', 'icon' => 'ti ti-briefcase'],
        ['title' => __('Foreman Coach'), 'desc' => __('Site execution, sequencing, QA, variations, daily plans.'), 'route' => 'titan.zero.foreman', 'icon' => 'ti ti-helmet'],
        ['title' => __('Standards & Compliance'), 'desc' => __('NCC, AS/NZS, SWMS, prestart, toolbox talks with citations.'), 'route' => 'titan.zero.compliance', 'icon' => 'ti ti-shield-check'],
        ['title' => __('Chat & Standards'), 'desc' => __('Ask questions across your guides + standards library.'), 'route' => 'titan.zero.chat', 'icon' => 'ti ti-message-circle'],
    ];

    $quick = [
        ['title' => __('Wizards'), 'route' => 'titan.zero.wizards', 'icon' => 'ti ti-wand'],
        ['title' => __('Generators'), 'route' => 'titan.zero.generators', 'icon' => 'ti ti-bolt'],
        ['title' => __('Templates'), 'route' => 'titan.zero.templates', 'icon' => 'ti ti-template'],
        ['title' => __('Help'), 'route' => 'titan.zero.help', 'icon' => 'ti ti-life-buoy'],
    ];
@endphp

@extends('layouts.app')

@section('content')
<div class="container py-3">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">{{ __('Titan Zero') }}</h4>
            <small class="text-muted">{{ __('Your on-page assistant for Worksuite + standards & guides (with citations).') }}</small>
        </div>
        @if(Route::has('titan.zero.chat'))
            <a href="{{ route('titan.zero.chat') }}" class="btn btn-primary">
                <i class="ti ti-message-circle me-1"></i>{{ __('Open Chat') }}
            </a>
        @endif
    </div>

    {{-- Coach Cards --}}
    <div class="row g-3 mb-3">
        @foreach($coachCards as $c)
            @continue(!Route::has($c['route']))
            <div class="col-12 col-md-6 col-lg-3">
                <a href="{{ route($c['route']) }}" class="text-decoration-none">
                    <div class="card h-100">
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-2">
                                    <i class="{{ $c['icon'] }} f-22"></i>
                                </div>
                                <div class="fw-semibold">{{ $c['title'] }}</div>
                            </div>
                            <div class="text-muted small">{{ $c['desc'] }}</div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    {{-- Quick Actions --}}
    <div class="card">
        <div class="card-body">
            <div class="fw-semibold mb-2">{{ __('Quick Actions') }}</div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($quick as $q)
                    @continue(!Route::has($q['route']))
                    <a class="btn btn-outline-secondary btn-sm" href="{{ route($q['route']) }}">
                        <i class="{{ $q['icon'] }} me-1"></i>{{ $q['title'] }}
                    </a>
                @endforeach
            </div>
        </div>
    </div>

</div>
@endsection
