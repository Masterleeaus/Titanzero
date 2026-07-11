{{-- Titan Zero Pass 2: Options panel --}}
<div class="card mb-3">
    <div class="card-header">
        <strong>{{ __('Generation options') }}</strong>
    </div>
    <div class="card-body row g-3">
        {{-- Language --}}
        <div class="col-md-4">
            <label class="form-label">{{ __('Language') }}</label>
            {{-- Existing select from template should be reused; this is only structural UI. --}}
        </div>

        {{-- Tone --}}
        <div class="col-md-4">
            <label class="form-label">{{ __('Tone') }}</label>
        </div>

        {{-- Creativity / temperature --}}
        <div class="col-md-4">
            <label class="form-label">{{ __('Creativity') }}</label>
            <small class="text-muted d-block">{{ __('Lower = more factual, higher = more creative.') }}</small>
        </div>
    </div>
</div>
