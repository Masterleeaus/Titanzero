@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12 mb-3">
            <h3 class="mb-1">Titan Zero Wizards</h3>
            <p class="text-muted mb-0">Structured help (no generic chat). Choose a wizard below.</p>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><strong>Explain This Page</strong></div>
                <div class="card-body">
                    <p class="text-muted">Explains what you’re looking at and what the fields mean (read-only).</p>
                    <button class="btn btn-primary" id="tzExplainBtn">Run Explain Wizard</button>
                    <pre class="mt-3 p-3 bg-dark text-white small rounded" style="min-height:120px" id="tzExplainOut"></pre>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header"><strong>Standards Q&amp;A (Citations)</strong></div>
                <div class="card-body">
                    <p class="text-muted">Ask about NCC / AS/NZS and internal guides. Returns citations/excerpts.</p>
                    <div class="mb-2">
                        <input type="text" class="form-control" id="tzStdQ" placeholder="e.g. What does NCC say about wet area waterproofing?">
                    </div>
                    <button class="btn btn-outline-primary" id="tzStdBtn">Ask</button>
                    <pre class="mt-3 p-3 bg-dark text-white small rounded" style="min-height:120px" id="tzStdOut"></pre>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    function pageContext() {
        return {
            page: document.title || 'unknown',
            route: window.location.pathname,
            fields: []
        };
    }

    async function post(url, payload) {
        const res = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(payload)
        });
        return await res.json();
    }

    const explainBtn = document.getElementById('tzExplainBtn');
    if (explainBtn) {
        explainBtn.addEventListener('click', async () => {
            document.getElementById('tzExplainOut').textContent = 'Running...';
            const data = await post("{{ url('account/titan/zero/wizards/explain') }}", {
                text: "Explain this page",
                page_context: pageContext()
            });
            document.getElementById('tzExplainOut').textContent = JSON.stringify(data, null, 2);
        });
    }

    const stdBtn = document.getElementById('tzStdBtn');
    if (stdBtn) {
        stdBtn.addEventListener('click', async () => {
            document.getElementById('tzStdOut').textContent = 'Searching...';
            const q = document.getElementById('tzStdQ').value || '';
            const data = await post("{{ url('account/titan/zero/wizards/standards') }}", {
                question: q,
                page_context: pageContext()
            });
            document.getElementById('tzStdOut').textContent = JSON.stringify(data, null, 2);
        });
    }
})();
</script>
@endpush
