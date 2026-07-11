@extends('titanzero::layouts.master')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="mb-0">Titan Zero — Channels</h3>
            <small class="text-muted">Enable/disable channels and set basic configuration. Health checks are config-only (no external calls).</small>
        </div>
        <div class="text-end">
            <a href="{{ url('/account/settings/titanzero/dashboard') }}" class="btn btn-sm btn-outline-secondary">Back to Dashboard</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="POST" action="{{ url('/account/settings/titanzero/channels/save') }}">
        @csrf

        <div class="card">
            <div class="card-header">
                <strong>Channel Registry</strong>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th style="width: 260px;">Channel</th>
                                <th style="width: 120px;">Enabled</th>
                                <th>Configuration</th>
                                <th style="width: 220px;">Health</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($channels as $ch)
                                @php
                                    $key = $ch['key'] ?? '';
                                    $config = $ch['config'] ?? [];
                                    $health = $ch['health'] ?? [];
                                    $status = $health['status'] ?? 'unknown';
                                    $notes = $health['notes'] ?? [];
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $ch['label'] ?? ucfirst($key) }}</div>
                                        <div class="text-muted"><small>{{ $key }}</small></div>
                                    </td>
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                name="channels[{{ $key }}][enabled]"
                                                value="1"
                                                id="ch_{{ $key }}"
                                                {{ !empty($ch['enabled']) ? 'checked' : '' }}>
                                            <label class="form-check-label" for="ch_{{ $key }}">
                                                {{ !empty($ch['enabled']) ? 'On' : 'Off' }}
                                            </label>
                                        </div>
                                    </td>
                                    <td>
                                        @if($key === 'web')
                                            <div class="text-muted"><small>Uses the floating widget across /account pages.</small></div>
                                        @elseif(in_array($key, ['sms','whatsapp','voice']))
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label class="form-label mb-1"><small>From number</small></label>
                                                    <input type="text" class="form-control form-control-sm"
                                                        name="channels[{{ $key }}][config][from_number]"
                                                        value="{{ $config['from_number'] ?? '' }}"
                                                        placeholder="+61...">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label mb-1"><small>Note</small></label>
                                                    <input type="text" class="form-control form-control-sm"
                                                        name="channels[{{ $key }}][config][note]"
                                                        value="{{ $config['note'] ?? '' }}"
                                                        placeholder="Optional internal note">
                                                </div>
                                            </div>
                                            <div class="text-muted mt-2"><small>Twilio credentials are read from env (TWILIO_SID/TWILIO_TOKEN).</small></div>
                                        @elseif($key === 'email')
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label class="form-label mb-1"><small>From address</small></label>
                                                    <input type="email" class="form-control form-control-sm"
                                                        name="channels[{{ $key }}][config][from_address]"
                                                        value="{{ $config['from_address'] ?? '' }}"
                                                        placeholder="noreply@...">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label mb-1"><small>From name</small></label>
                                                    <input type="text" class="form-control form-control-sm"
                                                        name="channels[{{ $key }}][config][from_name]"
                                                        value="{{ $config['from_name'] ?? '' }}"
                                                        placeholder="Business Name">
                                                </div>
                                            </div>
                                            <div class="text-muted mt-2"><small>Mail configuration is read from env (MAIL_MAILER/MAIL_HOST).</small></div>
                                        @else
                                            <div class="text-muted"><small>No config fields.</small></div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center justify-content-between gap-2">
                                            <div>
                                                <span class="badge bg-{{ $status === 'ok' ? 'success' : ($status === 'needs_config' ? 'warning' : 'secondary') }}">
                                                    {{ strtoupper($status) }}
                                                </span>
                                                @if(!empty($notes))
                                                    <div class="text-muted mt-1"><small>{{ is_array($notes) ? implode(' ', $notes) : $notes }}</small></div>
                                                @endif
                                            </div>
                                            <button type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                onclick="titanzeroTestChannel('{{ $key }}')">
                                                Test
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted">No channels found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    <button class="btn btn-primary">Save Channels</button>
                </div>
            </div>
        </div>
    </form>

    <div class="modal fade" id="tzTestModal" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Channel Test</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <pre id="tzTestOutput" style="white-space: pre-wrap;" class="mb-0"></pre>
          </div>
        </div>
      </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function titanzeroTestChannel(key) {
    fetch("{{ url('/account/settings/titanzero/channels/test') }}/" + encodeURIComponent(key), {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        }
    })
    .then(r => r.json())
    .then(data => {
        const out = document.getElementById('tzTestOutput');
        out.textContent = JSON.stringify(data, null, 2);
        const modalEl = document.getElementById('tzTestModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    })
    .catch(err => {
        const out = document.getElementById('tzTestOutput');
        out.textContent = "Error: " + (err && err.message ? err.message : String(err));
        const modalEl = document.getElementById('tzTestModal');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    });
}
</script>
@endpush
