@php($pageTitle = 'Titan Zero • Coaches')
<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">Coaches</h3>
    <a class="btn btn-sm btn-outline-secondary" href="{{ url('account/titan/zero') }}">Titan Zero</a>
  </div>

  <div class="row g-3">
    @foreach($coaches as $key => $coach)
      <div class="col-md-6 col-lg-4">
        <div class="card h-100">
          <div class="card-body">
            <h5 class="card-title mb-1">{{ $coach['name'] ?? $key }}</h5>
            <div class="text-muted small mb-2"><code>{{ $key }}</code></div>
            <p class="card-text">{{ $coach['description'] ?? '' }}</p>
            <a class="btn btn-sm btn-primary" href="{{ route('titan.zero.coaches.show', $key) }}">Open</a>
          </div>
        </div>
      </div>
    @endforeach
  </div>
</div>
