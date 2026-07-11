@php($pageTitle = 'Titan Zero • Doctor')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-0">Titan Zero Doctor</h3>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('dashboard.admin.titanzero.library.index') }}">Standards Library</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>Check</th>
                        <th>Status</th>
                        <th>Detail</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($checks as $c)
                        <tr>
                            <td>{{ $c['name'] }}</td>
                            <td>
                                @if($c['ok'])
                                    <span class="badge bg-success">OK</span>
                                @else
                                    <span class="badge bg-danger">FAIL</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $c['detail'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
