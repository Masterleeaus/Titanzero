{{-- Titan Zero Pass 2: Result panel --}}
<div class="card mt-3 titan-zero-result">
    <div class="card-header d-flex justify-content-between align-items-center">
        <strong>{{ __('Generated result') }}</strong>
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="if(window.TitanZero && TitanZero.copy){TitanZero.copy()}">
            {{ __('Copy to clipboard') }}
        </button>
    </div>
    <div class="card-body">
        {{-- Existing textarea / result output will still be used; this wrapper just standardises the layout. --}}

        <div class="mt-3 d-flex flex-wrap gap-2">
            @if(config('aiassistant.integrations.docs_enabled'))
                <button type="button"
                        class="btn btn-sm btn-outline-primary"
                        onclick="window.TitanZero && TitanZero.sendTo('docs')">
                    {{ __('Send to Docs') }}
                </button>
            @endif

            @if(config('aiassistant.integrations.tasks_enabled'))
                <button type="button"
                        class="btn btn-sm btn-outline-primary"
                        onclick="window.TitanZero && TitanZero.sendTo('tasks')">
                    {{ __('Send to Tasks') }}
                </button>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.TitanZero = window.TitanZero || {};
    window.TitanZero.copy = function () {
        var el = document.querySelector('.titan-zero-result textarea, .titan-zero-result [data-result-text]');
        if (!el) return;
        var text = el.value || el.innerText || '';
        if (!text) return;
        navigator.clipboard && navigator.clipboard.writeText(text).catch(function(){});
    };

    window.TitanZero.sendTo = function (target) {
        var el = document.querySelector('.titan-zero-result textarea, .titan-zero-result [data-result-text]');
        if (!el) return;
        var text = el.value || el.innerText || '';
        if (!text) return;

        var urlDocs  = '{{ route('titan-zero.send-docs') }}';
        var urlTasks = '{{ route('titan-zero.send-tasks') }}';
        var url      = target === 'docs' ? urlDocs : urlTasks;

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ content: text })
        }).then(function (response) {
            return response.json().catch(function () { return {}; });
        }).then(function (json) {
            // You can enhance this with toast notifications later.
        }).catch(function () {});
    };
</script>
@endpush
