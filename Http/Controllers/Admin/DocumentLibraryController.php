<?php

namespace Modules\TitanZero\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanZero\Http\Requests\Admin\DocumentUploadRequest;
use Modules\TitanZero\Entities\TitanZeroDocument;
use Modules\TitanZero\Entities\TitanZeroImport;
use Modules\TitanZero\Services\Docs\ImportService;

class DocumentLibraryController extends Controller
{
    public function index()
    {
        $docs = TitanZeroDocument::query()->orderByDesc('id')->paginate(25);
        return view('titanzero::admin.library.index', compact('docs'));
    }

    public function upload()
    {
        return view('titanzero::admin.library.upload');
    }

    public function store(DocumentUploadRequest $request, ImportService $importService)
    {
        $file = $request->file('pdf');
        $title = $request->input('title') ?: $file->getClientOriginalName();

        $import = $importService->importPdf($file->getRealPath(), $title, [
            'original_name' => $file->getClientOriginalName(),
            'mime' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);

        return redirect()->route('dashboard.admin.titanzero.library.imports')
            ->with('status', 'Import queued/processed: #'.$import->id);
    }

    public function show($id)
    {
        $doc = TitanZeroDocument::query()->findOrFail($id);
        $chunks = $doc->chunks()->orderBy('chunk_index')->paginate(50);
        return view('titanzero::admin.library.show', compact('doc','chunks'));
    }

    public function imports()
    {
        $imports = TitanZeroImport::query()->orderByDesc('id')->paginate(25);
        return view('titanzero::admin.library.imports', compact('imports'));
    }
}
