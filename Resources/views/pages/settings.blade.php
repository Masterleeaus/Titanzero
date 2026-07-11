@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">{{ __('TitanZero Settings') }}</h4>
            <small class="text-muted">{{ __('Enable or disable TitanZero AI features for your organisation.') }}</small>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ route('titan.zero.settings.save') }}">
        @csrf
        <div class="card">
            <div class="card-header fw-semibold">{{ __('Cleaning Intelligence Features') }}</div>
            <div class="card-body">
                @php
                    $enabled = \Illuminate\Support\Facades\Cache::get('titanzero.features.' . (auth()->user()->company_id ?? 0), []);
                    $available = [
                        ['key' => 'booking_slots',          'label' => __('Smart Booking Slot Suggestions')],
                        ['key' => 'cleaner_match',           'label' => __('Intelligent Cleaner Matching')],
                        ['key' => 'auto_fill_instructions',  'label' => __('Auto-fill Special Instructions')],
                        ['key' => 'price_suggestion',        'label' => __('Price Suggestion')],
                        ['key' => 'rebooking_suggestion',    'label' => __('Rebooking Suggestion')],
                        ['key' => 'sms_draft',               'label' => __('SMS Drafting Assistance')],
                        ['key' => 'complaint_triage',        'label' => __('Complaint Triage')],
                        ['key' => 'anomaly_detection',       'label' => __('Anomaly Detection')],
                        ['key' => 'automation_rules',        'label' => __('Automation Rule Suggestions')],
                    ];
                @endphp
                @foreach($available as $feature)
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox"
                               name="enabled_features[]"
                               value="{{ $feature['key'] }}"
                               id="feature_{{ $feature['key'] }}"
                               {{ in_array($feature['key'], $enabled) ? 'checked' : '' }}>
                        <label class="form-check-label" for="feature_{{ $feature['key'] }}">
                            {{ $feature['label'] }}
                        </label>
                    </div>
                @endforeach
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">{{ __('Save Settings') }}</button>
            </div>
        </div>
    </form>
</div>
@endsection
