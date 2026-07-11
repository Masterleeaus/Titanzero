@php($pageTitle = 'Titan Zero • Upload Standard')
<div class="container-fluid">
    <h3 class="mb-3">Upload PDF</h3>

    <div class="card p-3">
        <form method="POST" action="{{ route('dashboard.admin.titanzero.library.store') }}" enctype="multipart/form-data">
            @csrf

            <div class="mb-3">
                <label class="form-label">Title (optional)</label>
                <input type="text" name="title" class="form-control" value="{{ old('title') }}">
            </div>

            <div class="mb-3">
                <label class="form-label">PDF</label>
                <input type="file" name="pdf" class="form-control" accept="application/pdf" required>
                <div class="form-text">Max 50MB. Pass 4 uses pdftotext if installed.</div>
            </div>

            <button class="btn btn-primary">Upload & Import</button>
            <a class="btn btn-outline-secondary" href="{{ route('dashboard.admin.titanzero.library.index') }}">Back</a>
        </form>
    </div>
</div>
