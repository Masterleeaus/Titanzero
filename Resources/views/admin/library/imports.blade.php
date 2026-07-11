@php($pageTitle = 'Titan Zero • Imports')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-0">Imports</h3>
        <a class="btn btn-sm btn-outline-secondary" href="{{ route('dashboard.admin.titanzero.library.index') }}">Back to Library</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Document</th>
                        <th>Status</th>
                        <th>Message</th>
                        <th>Updated</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($imports as $imp)
                        <tr>
                            <td>{{ $imp->id }}</td>
                            <td>{{ $imp->document_id }}</td>
                            <td>{{ $imp->status }}</td>
                            <td class="text-muted">{{ $imp->message }}</td>
                            <td>{{ $imp->updated_at }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted">No imports yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $imports->links() }}</div>
</div>
