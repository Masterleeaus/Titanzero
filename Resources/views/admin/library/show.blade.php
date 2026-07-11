@php($pageTitle = 'Titan Zero • Document')
<div class="container-fluid">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div>
            <h3 class="mb-0">{{ $doc->title }}</h3>
      <a class="btn btn-sm btn-outline-primary ms-2" href="{{ route('dashboard.admin.titanzero.library.meta.edit', $document->id) }}">Metadata</a>
            <div class="text-muted">Source: {{ $doc->source }} • SHA: {{ $doc->sha256 }}</div>
        </div>
        <a class="btn btn-outline-secondary" href="{{ route('dashboard.admin.titanzero.library.index') }}">Back</a>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Preview</th>
                        <th>Len</th>
                        <th>Hash</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($chunks as $chunk)
                        <tr>
                            <td>{{ $chunk->chunk_index }}</td>
                            <td style="max-width: 800px;">
                                <div class="small text-muted">{{ \Illuminate\Support\Str::limit($chunk->content, 260) }}</div>
                            </td>
                            <td>{{ $chunk->meta['len'] ?? '' }}</td>
                            <td><code class="small">{{ $chunk->content_hash }}</code></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">{{ $chunks->links() }}</div>
</div>
