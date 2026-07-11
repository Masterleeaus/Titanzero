<?php

namespace Modules\TitanZero\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanZero\Services\Intent\IntentEngine;
use Modules\TitanZero\Services\Actions\ActionRouter;
use Modules\TitanZero\Actions\Handlers\ExplainPageHandler;
use Modules\TitanZero\Actions\Handlers\HelpFillFormHandler;
use Modules\TitanZero\Actions\Handlers\SummarizeStandardHandler;

class IntentController extends Controller
{
    public function resolve(Request $request, IntentEngine $engine)
    {
        $pageContext = (array)$request->input('page_context', []);
        $intent = $engine->resolve($request, $pageContext);

        return response()->json([
            'ok' => true,
            'intent' => $intent->toArray(),
        ]);
    }

    public function route(Request $request, IntentEngine $engine)
    {
        $pageContext = (array)$request->input('page_context', []);
        $intent = $engine->resolve($request, $pageContext);

        $router = new ActionRouter();
        // Register read-only foundation handlers
        $router->registerHandler(new ExplainPageHandler());
        $router->registerHandler(new HelpFillFormHandler());
        $router->registerHandler(new SummarizeStandardHandler(app(\Modules\TitanZero\Services\Retrieval\RetrievalEngine::class)));

        $res = $router->route($intent, [
            'page_context' => $pageContext,
        ]);

        return response()->json($res);
    }

    public function confirm(Request $request)
    {
        $runId = (int)$request->input('run_id');
        if ($runId <= 0) {
            return response()->json(['ok' => false, 'error' => 'run_id required'], 422);
        }

        $router = new ActionRouter();
        $router->registerHandler(new ExplainPageHandler());
        $router->registerHandler(new HelpFillFormHandler());
        $router->registerHandler(new SummarizeStandardHandler(app(\Modules\TitanZero\Services\Retrieval\RetrievalEngine::class)));

        $res = $router->confirmAndExecute($runId, [
            'page_context' => (array)$request->input('page_context', []),
            'text' => (string)$request->input('text', ''),
        ]);

        return response()->json($res);
    }
}
