{{-- Titan Zero Pass 2: Template landing page --}}
@extends('titanzero::layouts.master')

@section('content')
    <div class="container-fluid titan-zero-landing">
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3 mb-1">{{ __('Titan Zero') }}</h1>
                <p class="text-muted mb-0">
                    {{ __('Generate jobsite logs, SWMS drafts, quotes, and client emails using trade-ready AI templates.') }}
                </p>
            </div>
        </div>

        <div class="row row-cols-1 row-cols-md-3 g-3">
            {{-- Placeholder template cards; underlying data can be wired later. --}}
            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5">{{ __('Jobsite daily report') }}</h2>
                        <p class="text-muted small mb-2">
                            {{ __('Summarise today's work, weather, delays, and safety notes.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5">{{ __('SWMS / method statement draft') }}</h2>
                        <p class="text-muted small mb-2">
                            {{ __('Draft a method statement or SWMS outline ready for review.') }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="col">
                <div class="card h-100">
                    <div class="card-body">
                        <h2 class="h5">{{ __('Quote & scope of works') }}</h2>
                        <p class="text-muted small mb-2">
                            {{ __('Turn dot points into a client-ready quote or scope description.') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
