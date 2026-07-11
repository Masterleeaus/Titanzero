@php($pageTitle = 'Titan Zero • Coaches')
<div class="container-fluid">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <h3 class="mb-0">Coaches</h3>
    <a class="btn btn-sm btn-outline-secondary" href="{{ route('dashboard.admin.titanzero.library.index') }}">Standards Library</a>
  </div>

  @if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
  @endif

  <div class="card">
    <div class="table-responsive">
      <table class="table mb-0">
        <thead>
          <tr>
            <th>ID</th><th>Key</th><th>Name</th><th>Enabled</th><th></th>
          </tr>
        </thead>
        <tbody>
          @foreach($coaches as $c)
            <tr>
              <td>{{ $c->id }}</td>
              <td><code>{{ $c->key }}</code></td>
              <td>{{ $c->name }}</td>
              <td>{!! $c->is_enabled ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-secondary">No</span>' !!}</td>
              <td><a class="btn btn-sm btn-primary" href="{{ route('dashboard.admin.titanzero.coaches.edit', $c->id) }}">Edit</a></td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>
</div>
