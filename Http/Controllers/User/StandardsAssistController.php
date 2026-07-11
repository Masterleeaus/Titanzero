<?php

namespace Modules\TitanZero\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanZero\Services\Logging\AuditLogger;
use Modules\TitanZero\Services\Retrieval\RetrievalEngine;

class StandardsAssistController extends Controller
{
    public function search(Request $request, RetrievalEngine $engine, AuditLogger $audit)
    {
        $query = (string) $request->input('query', '');
        $results = $engine->search($query, 5);


        $audit->log(
            $request->user()?->id,
            'standards.search',
            $request->path(),
            $request->ip(),
            ['query'=>$query,'count'=>count($results),'doc_ids'=>array_values(array_unique(array_map(fn($r)=>$r['document_id'],$results)))]
        );

        return response()->json([
            'ok' => true,
            'mode' => 'standards_search',
            'query' => $query,
            'cards' => [[
                'type' => 'citations',
                'title' => 'Relevant Standards Snippets',
                'items' => array_map(function($r){
                    return [
                        'document_id' => $r['document_id'],
                        'document_title' => $r['document_title'],
                        'chunk_index' => $r['chunk_index'],
                        'content_hash' => $r['content_hash'],
                        'preview' => mb_substr($r['content'], 0, 420),
                    ];
                }, $results),
            ]],
            'results' => $results,
        ]);
    }
}
