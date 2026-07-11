@php($pageTitle = 'Titan Zero • Bulk Tag Documents')
<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">Bulk Tag Documents</h3>
    <a class="btn btn-sm btn-outline-secondary" href="{{ route('dashboard.admin.titanzero.library.index') }}">Back to Library</a>
    <a class="btn btn-sm btn-outline-warning ms-2" href="{{ route('dashboard.admin.titanzero.library.review') }}">Review Queue</a>
  </div>

  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-6">
          <label class="form-label">Search title</label>
          <input class="form-control" name="q" value="{{ $q }}" placeholder="e.g. SWMS, pricing, NCC">
        </div>
        <div class="col-md-3">
          <label class="form-label">Coach override filter</label>
          <input class="form-control" name="coach" value="{{ $coach }}" placeholder="business / compliance / foreman">
        </div>
        <div class="col-md-3">
          <label class="form-label">Has tag (key)</label>
          <input class="form-control" name="tag" value="{{ $tagKey }}" placeholder="standards / business / safety">
        </div>
        <div class="col-12">
          <button class="btn btn-primary">Filter</button>
          <a class="btn btn-outline-secondary" href="{{ route('dashboard.admin.titanzero.library.bulk') }}">Reset</a>
        </div>
      </form>
    </div>
  </div>

  <form method="POST" action="{{ route('dashboard.admin.titanzero.library.bulk.apply') }}">
    @csrf

    <div class="card mb-3">
      <div class="card-header">
        Apply to selected documents
        <div class="text-muted small">Tags will be merged (existing tags kept).</div>
      </div>
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-3">
            <label class="form-label">Doc type</label>
            <input class="form-control" name="doc_type" placeholder="standard|guide|book|checklist">
          </div>
          <div class="col-md-3">
            <label class="form-label">Authority</label>
            <input class="form-control" name="authority_level" placeholder="regulatory|industry_guide|best_practice|opinion">
          </div>
          <div class="col-md-3">
            <label class="form-label">Jurisdiction</label>
            <input class="form-control" name="jurisdiction" placeholder="AU|NSW|QLD|VIC">
          </div>
          <div class="col-md-3">
            <label class="form-label">Preferred weight</label>
            <input class="form-control" name="preferred_weight" type="number" min="0" max="100" placeholder="0-100">
          </div>

          <div class="col-md-3">
            <label class="form-label">Coach override</label>
            <input class="form-control" name="coach_override" placeholder="business|compliance|foreman|estimator|contracts">
          </div>

          <div class="col-md-3 d-flex align-items-end">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="is_superseded" value="1">
              <label class="form-check-label">Mark as superseded</label>
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Tags to add</label>
            <div class="row g-2">
              @foreach($tags as $t)
                <div class="col-md-4">
                  <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="tag_ids[]" value="{{ $t->id }}">
                    <span class="form-check-label">{{ $t->name }} <span class="text-muted small">({{ $t->key }})</span></span>
                  </label>
                </div>
              @endforeach
            </div>
          </div>

          <div class="col-12">
            <button class="btn btn-primary">Apply bulk update</button>
          </div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-header">
        Documents (select which to update)
      </div>
      <div class="table-responsive">
        <table class="table mb-0">
          <thead>
            <tr>
              <th style="width:48px;"><input type="checkbox" onclick="document.querySelectorAll('.tzDocCb').forEach(cb=>cb.checked=this.checked)"></th>
              <th>ID</th>
              <th>Title</th>
              <th>Meta</th>
              <th>Tags</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @foreach($docs as $d)
              <tr>
                <td><input class="tzDocCb" type="checkbox" name="ids[]" value="{{ $d->id }}"></td>
                <td>{{ $d->id }}</td>
                <td>{{ $d->title }}</td>
                <td class="small text-muted">
                  {{ $d->doc_type }} / {{ $d->authority_level }} / {{ $d->jurisdiction }}
                  @if($d->is_superseded) <span class="badge bg-warning text-dark">Superseded</span> @endif
                  @if($d->preferred_weight) <span class="badge bg-info text-dark">Preferred {{ $d->preferred_weight }}</span> @endif
                  @if($d->coach_override) <span class="badge bg-secondary">{{ $d->coach_override }}</span> @endif
                </td>
                <td class="small">
                  @foreach($d->tags as $t)
                    <span class="badge bg-light text-dark border">{{ $t->key }}</span>
                  @endforeach
                </td>
                <td><a class="btn btn-sm btn-outline-primary" href="{{ route('dashboard.admin.titanzero.library.meta.edit', $d->id) }}">Metadata</a></td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
      <div class="card-body">
        {{ $docs->links() }}
      </div>
    </div>
  </form>
</div>
