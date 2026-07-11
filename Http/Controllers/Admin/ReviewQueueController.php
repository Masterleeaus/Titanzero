<?php

namespace Modules\TitanZero\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\TitanZero\Entities\TitanZeroDocument;
use Modules\TitanZero\Entities\TitanZeroDocumentTag;

class ReviewQueueController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status', 'pending');
        $min = (int)$request->input('min', 35);

        $docs = TitanZeroDocument::query()
            ->with('tags')
            ->when($status === 'pending', function($b) use ($min) {
                $b->where('review_status', 'pending')
                  ->orWhere('classification_confidence', '<', $min);
            }, function($b) use ($status) {
                $b->where('review_status', $status);
            })
            ->orderBy('classification_confidence')
            ->orderByDesc('id')
            ->paginate(25)
            ->withQueryString();

        $tags = TitanZeroDocumentTag::query()->orderBy('group')->orderBy('name')->get();

        return view('titanzero::admin.library.review_queue', compact('docs','tags','status','min'));
    }

    public function approve(int $id)
    {
        $doc = TitanZeroDocument::query()->findOrFail($id);
        $doc->review_status = 'approved';
        $doc->reviewed_by = Auth::id();
        $doc->reviewed_at = now();
        $doc->classification_source = $doc->classification_source ?: 'manual';
        $doc->save();

        return redirect()->back()->with('status', 'Document approved.');
    }

    public function needsWork(int $id)
    {
        $doc = TitanZeroDocument::query()->findOrFail($id);
        $doc->review_status = 'needs_work';
        $doc->reviewed_by = Auth::id();
        $doc->reviewed_at = now();
        $doc->save();

        return redirect()->back()->with('status', 'Marked as needs work.');
    }

    public function applyTags(int $id, Request $request)
    {
        $doc = TitanZeroDocument::query()->with('tags')->findOrFail($id);
        $tagIds = $request->input('tag_ids', []);
        if (!is_array($tagIds)) $tagIds = [];
        $tagIds = array_values(array_filter(array_map('intval', $tagIds)));

        if (count($tagIds) > 0) {
            $existing = $doc->tags()->pluck('id')->toArray();
            $merged = array_values(array_unique(array_merge($existing, $tagIds)));
            $doc->tags()->sync($merged);
        }

        // If it now has tags, consider approving
        if ($doc->tags()->count() > 0) {
            $doc->review_status = 'approved';
            $doc->reviewed_by = Auth::id();
            $doc->reviewed_at = now();
            $doc->classification_source = 'manual';
            $doc->save();
        }

        return redirect()->back()->with('status', 'Tags applied.');
    }
}
