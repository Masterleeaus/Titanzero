<?php

namespace Modules\TitanZero\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanZero\Entities\TitanZeroDocument;
use Modules\TitanZero\Entities\TitanZeroDocumentTag;

class DocumentMetaController extends Controller
{
    public function edit(int $id)
    {
        $document = TitanZeroDocument::query()->with('tags')->findOrFail($id);
        $tags = TitanZeroDocumentTag::query()->orderBy('group')->orderBy('name')->get();
        $selected = $document->tags->pluck('id')->toArray();
        return view('titanzero::admin.library.meta', compact('document','tags','selected'));
    }

    public function update(int $id, Request $request)
    {
        $document = TitanZeroDocument::query()->with('tags')->findOrFail($id);

        $document->doc_type = $request->input('doc_type');
        $document->authority_level = $request->input('authority_level');
        $document->jurisdiction = $request->input('jurisdiction');
        $document->is_superseded = (bool)$request->input('is_superseded', false);
        $document->preferred_weight = (int)$request->input('preferred_weight', 0);
        $document->coach_override = $request->input('coach_override') ?: null;

        $document->save();

        $tagIds = $request->input('tag_ids', []);
        if (is_array($tagIds)) {
            $document->tags()->sync(array_map('intval', $tagIds));
        }

        return redirect()->route('dashboard.admin.titanzero.library.show', $document->id)->with('status', 'Metadata updated.');
    }
}
