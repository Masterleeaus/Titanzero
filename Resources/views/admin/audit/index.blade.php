@php($pageTitle = 'Titan Zero • Audit Logs')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-0">Audit Logs</h3>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('dashboard.admin.titanzero.doctor') }}">Doctor</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Action</th>
                        <th>Route</th>
                        <th>IP</th>
                        <th>Meta</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($logs as $l)
                        <tr>
                            <td>{{ $l->id }}</td>
                            <td>{{ $l->user_id }}</td>
                            <td><code>{{ $l->action }}</code></td>
                            <td class="text-muted small">{{ $l->route }}</td>
                            <td class="text-muted small">{{ $l->ip }}</td>
                            <td style="max-width: 520px;">
                                <pre class="small mb-0">{{ json_encode($l->meta, JSON_PRETTY_PRINT) }}</pre>
                            </td>
                            <td class="text-muted small">{{ $l->created_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $logs->links() }}</div>
</div>
