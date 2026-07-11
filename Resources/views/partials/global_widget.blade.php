{{-- Titan Zero Pass 7: Global widget (include this in your main layout) --}}
@if(config('aiassistant.global_widget.enabled', true))
<div id="titan-zero-widget" class="titan-zero-widget-closed">
    <div class="titan-zero-widget-toggle" onclick="window.TitanGlobalAssist && TitanGlobalAssist.toggle()">
        <span class="titan-zero-icon">⚡</span>
        <span class="titan-zero-label d-none d-md-inline">{{ __('Titan Zero') }}</span>
    </div>

    <div class="titan-zero-panel">
        <div class="titan-zero-panel-header d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ __('Titan Zero') }}</strong>
                <div class="small text-muted">{{ __('Ask anything about this page.') }}</div>
            </div>
            <button type="button" class="btn btn-sm btn-link text-muted" onclick="window.TitanGlobalAssist && TitanGlobalAssist.toggle()">&times;</button>
        </div>

        <div class="titan-zero-panel-body">
            <div class="mb-2 small text-muted">
                {{ __('Titan Zero uses the current page title and URL as context. For best results, mention what you are working on.') }}
            </div>
            <textarea class="form-control mb-2" rows="3" placeholder="{{ __('How can Titan Zero help on this page?') }}" id="titan-zero-input"></textarea>
            <button type="button" class="btn btn-primary w-100" onclick="window.TitanGlobalAssist && TitanGlobalAssist.send()">
                {{ __('Ask Titan Zero') }}
            </button>

            <div class="titan-zero-panel-result mt-3 small" id="titan-zero-result" style="display:none;"></div>
        </div>
    </div>
</div>

@push('styles')
<style>
    #titan-zero-widget {
        position: fixed;
        right: 1.5rem;
        bottom: 1.5rem;
        z-index: 1060;
        font-size: 14px;
    }

    .titan-zero-widget-toggle {
        background: #111827;
        color: #ffffff;
        border-radius: 999px;
        padding: 0.5rem 0.9rem;
        box-shadow: 0 4px 12px rgba(15, 23, 42, 0.45);
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
    }

    .titan-zero-icon {
        font-size: 1.1rem;
    }

    .titan-zero-panel {
        position: absolute;
        right: 0;
        bottom: 3.25rem;
        width: 320px;
        max-width: calc(100vw - 3rem);
        background: #ffffff;
        border-radius: 0.75rem;
        box-shadow: 0 18px 45px rgba(15, 23, 42, 0.35);
        border: 1px solid rgba(148, 163, 184, 0.35);
        overflow: hidden;
        display: none;
    }

    .titan-zero-panel-header {
        padding: 0.6rem 0.9rem;
        border-bottom: 1px solid rgba(148, 163, 184, 0.35);
        background: #f8fafc;
    }

    .titan-zero-panel-body {
        padding: 0.9rem;
        max-height: 360px;
        overflow-y: auto;
    }

    .titan-zero-panel-result {
        white-space: pre-wrap;
        border-radius: 0.5rem;
        background: #f9fafb;
        padding: 0.5rem 0.6rem;
        border: 1px solid rgba(148, 163, 184, 0.35);
    }

    .titan-zero-widget-open .titan-zero-panel {
        display: block;
    }
</style>
@endpush

@push('scripts')
<script>
    window.TitanGlobalAssist = window.TitanGlobalAssist || (function () {
        var widget, panel, input, result;

        function init() {
            widget = document.getElementById('titan-zero-widget');
            if (!widget) return;
            panel = widget.querySelector('.titan-zero-panel');
            input = document.getElementById('titan-zero-input');
            result = document.getElementById('titan-zero-result');
        }

        function toggle() {
            if (!widget) init();
            if (!widget) return;

            widget.classList.toggle('titan-zero-widget-open');
        }

        function send() {
            if (!widget) init();
            if (!widget || !input) return;

            var question = (input.value || '').trim();
            if (!question) return;

            if (result) {
                result.style.display = 'block';
                result.innerText = '{{ __('Asking Titan Zero...') }}';
            }

            var payload = {
                question: question,
                page: document.title || '',
                url: window.location.href || ''
            };

            var tokenEl = document.querySelector('meta[name="csrf-token"]');
            var csrf = tokenEl ? tokenEl.getAttribute('content') : '';

            fetch('{{ route('titan-zero.global.assist') }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(payload)
            })
                .then(function (response) {
                    return response.json().catch(function () { return {}; });
                })
                .then(function (json) {
                    if (!result) return;
                    if (json && json.status === 'success') {
                        result.innerText = json.answer || '';
                    } else if (json && json.message) {
                        result.innerText = json.message;
                    } else {
                        result.innerText = '{{ __('Something went wrong. Please try again.') }}';
                    }
                })
                .catch(function () {
                    if (result) {
                        result.innerText = '{{ __('Something went wrong. Please try again.') }}';
                    }
                });
        }

        return {
            toggle: toggle,
            send: send
        };
    })();
</script>
@endpush
@endif
