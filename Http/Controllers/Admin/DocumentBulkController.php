<?php

namespace Modules\TitanZero\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanZero\Entities\TitanZeroDocument;
use Modules\TitanZero\Entities\TitanZeroDocumentTag;

class DocumentBulkController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string)$request->input('q',''));
        $coach = trim((string)$request->input('coach',''));
        $tagKey = trim((string)$request->input('tag',''));

        $docs = TitanZeroDocument::query()
            ->with('tags')
            ->when($q !== '', fn($b) => $b->where('title','like', '%'.$q.'%'))
            ->when($coach !== '', fn($b) => $b->where('coach_override', $coach))
            ->when($tagKey !== '', function($b) use ($tagKey) {
                $b->whereHas('tags', fn($t) => $t->where('key', $tagKey));
            })
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $tags = TitanZeroDocumentTag::query()->orderBy('group')->orderBy('name')->get();

        return view('titanzero::admin.library.bulk', compact('docs','tags','q','coach','tagKey'));
    }

    public function apply(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || count($ids) === 0) {
            return redirect()->back()->with('status', 'No documents selected.');
        }

        $ids = array_map('intval', $ids);

        $docType = $request->input('doc_type');
        $authority = $request->input('authority_level');
        $jurisdiction = $request->input('jurisdiction');
        $isSuperseded = $request->has('is_superseded') ? (bool)$request->input('is_superseded') : null;
        $preferred = $request->input('preferred_weight');
        $coachOverride = $request->input('coach_override');

        $tagIds = $request->input('tag_ids', []);
        if (!is_array($tagIds)) $tagIds = [];
        $tagIds = array_values(array_filter(array_map('intval', $tagIds)));

        $docs = TitanZeroDocument::query()->whereIn('id', $ids)->get();

        foreach ($docs as $d) {
            if ($docType !== null && $docType !== '') $d->doc_type = $docType;
            if ($authority !== null && $authority !== '') $d->authority_level = $authority;
            if ($jurisdiction !== null && $jurisdiction !== '') $d->jurisdiction = $jurisdiction;
            if ($preferred !== null && $preferred !== '') $d->preferred_weight = (int)$preferred;
            if ($coachOverride !== null && $coachOverride !== '') $d->coach_override = $coachOverride;

            if ($isSuperseded !== null) $d->is_superseded = (bool)$isSuperseded;

            $d->save();

            if (count($tagIds) > 0) {
                // merge tags (do not remove existing)
                $existing = $d->tags()->pluck('id')->toArray();
                $merged = array_values(array_unique(array_merge($existing, $tagIds)));
                $d->tags()->sync($merged);
            }
        }

        return redirect()->back()->with('status', 'Bulk update applied to '.count($ids).' documents.');
    }
}
