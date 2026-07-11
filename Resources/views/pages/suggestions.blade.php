@extends('layouts.app')

@section('content')
<div class="container py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0">{{ __('AI Suggestion Dashboard') }}</h4>
            <small class="text-muted">{{ __('Test and explore TitanZero cleaning intelligence features.') }}</small>
        </div>
        @if(Route::has('titan.zero.settings'))
            <a href="{{ route('titan.zero.settings') }}" class="btn btn-outline-secondary btn-sm">
                <i class="ti ti-settings me-1"></i>{{ __('Feature Settings') }}
            </a>
        @endif
    </div>

    <div class="row g-3">
        @foreach($features as $feature)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="fw-semibold mb-1">{{ $feature['label'] }}</div>
                        <div class="text-muted small mb-3">{{ $feature['description'] }}</div>
                        <button class="btn btn-sm btn-outline-primary titan-test-btn"
                                data-feature="{{ $feature['key'] }}"
                                data-url="{{ route('titan.zero.suggestions.test') }}">
                            {{ __('Test') }}
                        </button>
                        <div class="titan-result mt-2 small text-muted" style="display:none;"></div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
document.querySelectorAll('.titan-test-btn').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var feature = this.dataset.feature;
        var url = this.dataset.url;
        var resultEl = this.closest('.card-body').querySelector('.titan-result');
        resultEl.style.display = 'block';
        resultEl.textContent = '{{ __("Loading...") }}';

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : ''
            },
            body: JSON.stringify({ feature: feature, context: {} })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var msg = data.result && data.result.message ? data.result.message :
                      data.draft_text ? data.draft_text :
                      JSON.stringify(data, null, 2);
            resultEl.textContent = msg;
        })
        .catch(function(e) {
            resultEl.textContent = 'Error: ' + e.message;
        });
    });
});
</script>
@endsection
