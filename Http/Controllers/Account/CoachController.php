<?php

namespace Modules\TitanZero\Http\Controllers\Account;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanZero\Services\Coach\CoachRegistry;
use Modules\TitanZero\Services\Assist\StandardsAnswerBuilder;

class CoachController extends Controller
{
    public function index(CoachRegistry $registry)
    {
        $coaches = $registry->all();
        return view('titanzero::account.coach.index', compact('coaches'));
    }

    public function show(string $coachKey, CoachRegistry $registry)
    {
        $coach = $registry->get($coachKey);
        abort_if(!$coach, 404);
        return view('titanzero::account.coach.show', compact('coach'));
    }

    public function ask(string $coachKey, Request $request, CoachRegistry $registry, StandardsAnswerBuilder $builder)
    {
        $coach = $registry->get($coachKey);
        abort_if(!$coach, 404);

        $question = (string)$request->input('question','');
        $pageContext = (array)$request->input('page_context', []);

        $rules = $coach['rules'] ?? [];
        $filters = $rules['retrieval_filters'] ?? [];
        $filters['coach_key'] = $coachKey;

        if (method_exists($builder, 'buildWithFilters')) {
            return response()->json($builder->buildWithFilters($question, $pageContext, $filters, $coach));
        }

        $res = $builder->build($question, $pageContext);
        $res['coach'] = ['key'=>$coachKey, 'name'=>$coach['name'] ?? $coachKey];
        return response()->json($res);
    }
}
