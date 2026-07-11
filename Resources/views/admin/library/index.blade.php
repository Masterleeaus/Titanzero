@php($pageTitle = 'Titan Zero • Standards Library')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h3 class="mb-0">Standards Library</h3>
      <a class="btn btn-sm btn-outline-primary ms-2" href="{{ route('dashboard.admin.titanzero.library.bulk') }}">Bulk Tag</a>
      <a class="btn btn-sm btn-outline-warning ms-2" href="{{ route('dashboard.admin.titanzero.library.review') }}">Review Queue</a>
        <div class="d-flex gap-2">
            <a class="btn btn-sm btn-primary" href="{{ route('dashboard.admin.titanzero.library.upload') }}">Upload PDF</a>
            <a class="btn btn-sm btn-outline-secondary" href="{{ route('dashboard.admin.titanzero.library.imports') }}">Imports</a>
        </div>
    <a class="btn btn-sm btn-outline-secondary" href="{{ route('dashboard.admin.titanzero.doctor') }}">Doctor</a>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Title</th>
                        <th>Source</th>
                        <th>Chunks</th>
                        <th>Updated</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($docs as $doc)
                        <tr>
                            <td>{{ $doc->id }}</td>
                            <td>{{ $doc->title }}</td>
                            <td>{{ $doc->source }}</td>
                            <td>{{ $doc->chunks()->count() }}</td>
                            <td>{{ $doc->updated_at }}</td>
                            <td class="text-end">
                                <a class="btn btn-sm btn-outline-primary" href="{{ route('dashboard.admin.titanzero.library.show', $doc->id) }}">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No documents imported yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $docs->links() }}
    </div>
</div>
