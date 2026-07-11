<?php

namespace Modules\TitanZero\Http\Controllers\User;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanZero\Services\Logging\AuditLogger;
use Modules\TitanZero\Services\Assist\StandardsAnswerBuilder;

class StandardsGroundedController extends Controller
{
    public function assist(Request $request, StandardsAnswerBuilder $builder, AuditLogger $audit)
    {
        $question = (string) $request->input('question', '');
        $pageContext = (array) $request->input('page_context', []);


        $audit->log(
            $request->user()?->id,
            'assist.standards',
            $request->path(),
            $request->ip(),
            ['question'=>$question,'count'=>count(($builder->build($question,$pageContext)['results'] ?? []))]
        );

        return response()->json(
            $builder->build($question, $pageContext)
        );
    }
}
