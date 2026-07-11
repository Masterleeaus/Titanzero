@php($pageTitle = 'Titan Zero • Edit Coach')
<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">Edit Coach</h3>
    <a class="btn btn-sm btn-outline-secondary" href="{{ route('dashboard.admin.titanzero.coaches.index') }}">Back</a>
  </div>

  <form method="POST" action="{{ route('dashboard.admin.titanzero.coaches.update', $coach->id) }}">
    @csrf
    <div class="card mb-3">
      <div class="card-body">
        <div class="mb-3">
          <label class="form-label">Name</label>
          <input class="form-control" name="name" value="{{ $coach->name }}">
        </div>
        <div class="mb-3">
          <label class="form-label">Description</label>
          <textarea class="form-control" name="description" rows="3">{{ $coach->description }}</textarea>
        </div>
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" name="is_enabled" value="1" {{ $coach->is_enabled ? 'checked' : '' }}>
          <label class="form-check-label">Enabled</label>
        </div>
        <div class="mb-3">
          <label class="form-label">Rules JSON</label>
          <textarea class="form-control" name="rules_json" rows="12">{{ json_encode($coach->rules ?? [], JSON_PRETTY_PRINT) }}</textarea>
          <div class="text-muted small mt-1">Use <code>retrieval_filters</code> to set include/exclude tag keys and other filters.</div>
        </div>
        <button class="btn btn-primary">Save</button>
      </div>
    </div>
  </form>
</div>
