<?php

namespace Modules\TitanZero\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Modules\TitanZero\Services\Intent\IntentEngine;
use Modules\TitanZero\Services\Actions\ActionRouter;
use Modules\TitanZero\Actions\Handlers\ExplainPageHandler;
use Modules\TitanZero\Actions\Handlers\HelpFillFormHandler;
use Modules\TitanZero\Actions\Handlers\SummarizeStandardHandler;

class WizardController extends Controller
{
    public function index()
    {
        return view('titanzero::wizards.index');
    }

    public function explainPage(Request $request, IntentEngine $engine)
    {
        $pageContext = (array) $request->input('page_context', []);
        $request->merge(['text' => $request->input('text', 'Explain this page')]);

        $intent = $engine->resolve($request, $pageContext);

        $router = new ActionRouter();
        $router->registerHandler(new ExplainPageHandler());
        $router->registerHandler(new HelpFillFormHandler());
        $router->registerHandler(new SummarizeStandardHandler());

        $res = $router->route($intent, ['page_context' => $pageContext]);

        return response()->json($res);
    }

    public function standardsQa(Request $request, IntentEngine $engine)
    {
        // Force summarize_standard intent path by including standard keywords if missing.
        $q = (string) $request->input('question', '');
        $text = trim($q) !== '' ? $q : (string)$request->input('text', '');
        if (stripos($text, 'standard') === false && stripos($text, 'NCC') === false && stripos($text, 'AS') === false) {
            $text = 'Standard question: ' . $text;
        }
        $request->merge(['text' => $text]);

        $pageContext = (array) $request->input('page_context', []);
        $intent = $engine->resolve($request, $pageContext);

        $router = new ActionRouter();
        $router->registerHandler(new SummarizeStandardHandler());

        $res = $router->route($intent, ['page_context' => $pageContext, 'question' => $text]);

        return response()->json($res);
    }
}
