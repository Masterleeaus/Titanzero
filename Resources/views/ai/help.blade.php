{{-- Titan Zero Pass 4: Help & examples --}}
@extends('titanzero::layouts.master')

@section('content')
    <div class="container-fluid titan-zero-help">
        <div class="row mb-4">
            <div class="col">
                <h1 class="h3 mb-1">{{ __('Titan Zero help & examples') }}</h1>
                <p class="text-muted mb-0">
                    {{ __('Use these examples to get the best results from Titan Zero for construction and trade workflows.') }}
                </p>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <strong>{{ __('Jobsite daily report') }}</strong>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">{{ __('Example prompt') }}:</p>
                        <pre class="small mb-2">Summarise today&apos;s work at the Smith Street renovation, include weather, tasks completed, delays, and safety notes.</pre>
                        <p class="text-muted small mb-1">{{ __('Best for') }}:</p>
                        <ul class="small mb-0">
                            <li>{{ __('End-of-day site summaries for clients or internal notes.') }}</li>
                            <li>{{ __('Recording delays and safety issues in clear language.') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <strong>{{ __('SWMS / method statement draft') }}</strong>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">{{ __('Example prompt') }}:</p>
                        <pre class="small mb-2">Create a draft method statement for installing overhead lighting in an occupied office, including major steps and safety controls.</pre>
                        <p class="text-muted small mb-1">{{ __('Best for') }}:</p>
                        <ul class="small mb-0">
                            <li>{{ __('Early SWMS drafts that will be reviewed against company standards.') }}</li>
                            <li>{{ __('Helping supervisors structure work methods before final approval.') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <strong>{{ __('Quote & scope of works') }}</strong>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">{{ __('Example prompt') }}:</p>
                        <pre class="small mb-2">Turn these bullet points into a client-ready scope of works for a bathroom renovation: remove existing tiles; waterproof; install 600x600 porcelain tiles; new frameless shower screen; wall-hung vanity.</pre>
                        <p class="text-muted small mb-1">{{ __('Best for') }}:</p>
                        <ul class="small mb-0">
                            <li>{{ __('Turning rough notes into clear, client-facing descriptions.') }}</li>
                            <li>{{ __('Keeping language consistent across all quotes and scopes.') }}</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header">
                        <strong>{{ __('Client email follow-up') }}</strong>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">{{ __('Example prompt') }}:</p>
                        <pre class="small mb-2">Write a polite follow-up email to a client about an unpaid invoice for the Smith Street roofing job, friendly but firm, offering a call if they have any questions.</pre>
                        <p class="text-muted small mb-1">{{ __('Best for') }}:</p>
                        <ul class="small mb-0">
                            <li>{{ __('Client emails that sound professional and consistent.') }}</li>
                            <li>{{ __('Chasing payments without sounding aggressive.') }}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
