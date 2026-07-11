@php($pageTitle = 'Titan Zero • Document Metadata')
<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Metadata</h3>
      <div class="text-muted small">{{ $document->title }}</div>
    </div>
    <a class="btn btn-sm btn-outline-secondary" href="{{ route('dashboard.admin.titanzero.library.show', $document->id) }}">Back</a>
  </div>

  <form method="POST" action="{{ route('dashboard.admin.titanzero.library.meta.update', $document->id) }}">
    @csrf

    <div class="card mb-3">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-4">
            <label class="form-label">Doc type</label>
            <input class="form-control" name="doc_type" value="{{ $document->doc_type }}">
            <div class="text-muted small">standard | guide | book | checklist</div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Authority</label>
            <input class="form-control" name="authority_level" value="{{ $document->authority_level }}">
            <div class="text-muted small">regulatory | industry_guide | best_practice | opinion</div>
          </div>
          <div class="col-md-4">
            <label class="form-label">Jurisdiction</label>
            <input class="form-control" name="jurisdiction" value="{{ $document->jurisdiction }}">
            <div class="text-muted small">AU | NSW | QLD | VIC etc</div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Preferred weight (0-100)</label>
            <input class="form-control" name="preferred_weight" type="number" min="0" max="100" value="{{ $document->preferred_weight }}">
          </div>
          <div class="col-md-4">
            <label class="form-label">Coach override</label>
            <input class="form-control" name="coach_override" value="{{ $document->coach_override }}">
            <div class="text-muted small">business | compliance | foreman | estimator | contracts</div>
          </div>
          <div class="col-md-4 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="is_superseded" value="1" {{ $document->is_superseded ? 'checked' : '' }}>
              <label class="form-check-label">Superseded</label>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        Tags
        <div class="text-muted small">Select domains/roles to route docs to coaches.</div>
      </div>
      <div class="card-body">
        <div class="row g-2">
          @foreach($tags as $t)
            <div class="col-md-4">
              <label class="form-check">
                <input class="form-check-input" type="checkbox" name="tag_ids[]" value="{{ $t->id }}" {{ in_array($t->id, $selected) ? 'checked' : '' }}>
                <span class="form-check-label">{{ $t->name }} <span class="text-muted small">({{ $t->group }})</span></span>
              </label>
            </div>
          @endforeach
        </div>
      </div>
    </div>

    <div class="mt-3">
      <button class="btn btn-primary">Save metadata</button>
    </div>
  </form>
</div>
