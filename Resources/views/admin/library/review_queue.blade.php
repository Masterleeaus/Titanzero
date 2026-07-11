@php($pageTitle = 'Titan Zero • Review Queue')
<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Review Queue</h3>
      <div class="text-muted small">Docs needing review (low confidence, missing tags, or pending).</div>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-sm btn-outline-secondary" href="{{ route('dashboard.admin.titanzero.library.index') }}">Library</a>
      <a class="btn btn-sm btn-outline-primary" href="{{ route('dashboard.admin.titanzero.library.bulk') }}">Bulk Tag</a>
    </div>
  </div>

  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <div class="card mb-3">
    <div class="card-body">
      <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select class="form-select" name="status">
            @foreach(['pending','approved','needs_work'] as $s)
              <option value="{{ $s }}" {{ $status === $s ? 'selected' : '' }}>{{ $s }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-3">
          <label class="form-label">Min confidence</label>
          <input class="form-control" name="min" type="number" min="0" max="100" value="{{ $min }}">
        </div>
        <div class="col-12">
          <button class="btn btn-primary">Filter</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Confidence</th>
            <th>Status</th>
            <th>Tags</th>
            <th>Quick actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($docs as $d)
            <tr>
              <td>{{ $d->id }}</td>
              <td class="fw-semibold">{{ $d->title }}</td>
              <td><span class="badge bg-info text-dark">{{ (int)$d->classification_confidence }}</span></td>
              <td><span class="badge bg-secondary">{{ $d->review_status }}</span></td>
              <td class="small">
                @foreach($d->tags as $t)
                  <span class="badge bg-light text-dark border">{{ $t->key }}</span>
                @endforeach
              </td>
              <td>
                <div class="d-flex flex-wrap gap-2">
                  <a class="btn btn-sm btn-outline-primary" href="{{ route('dashboard.admin.titanzero.library.meta.edit', $d->id) }}">Metadata</a>
                  <form method="POST" action="{{ route('dashboard.admin.titanzero.review.approve', $d->id) }}">
                    @csrf
                    <button class="btn btn-sm btn-success">Approve</button>
                  </form>
                  <form method="POST" action="{{ route('dashboard.admin.titanzero.review.needswork', $d->id) }}">
                    @csrf
                    <button class="btn btn-sm btn-warning">Needs work</button>
                  </form>
                </div>

                <form class="mt-2" method="POST" action="{{ route('dashboard.admin.titanzero.review.applyTags', $d->id) }}">
                  @csrf
                  <div class="row g-2">
                    <div class="col-12 small text-muted">Add tags (merge):</div>
                    @foreach($tags as $t)
                      <div class="col-md-4">
                        <label class="form-check">
                          <input class="form-check-input" type="checkbox" name="tag_ids[]" value="{{ $t->id }}">
                          <span class="form-check-label small">{{ $t->key }}</span>
                        </label>
                      </div>
                    @endforeach
                    <div class="col-12">
                      <button class="btn btn-sm btn-outline-primary">Apply tags + approve</button>
                    </div>
                  </div>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    <div class="card-body">
      {{ $docs->links() }}
    </div>
  </div>
</div>
