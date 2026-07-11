<?php

namespace Modules\TitanZero\Http\Controllers;

use App\Http\Controllers\AccountBaseController;
use Illuminate\Http\Request;
use Modules\TitanZero\Http\Requests\TitanZeroActionRequest;
use Modules\TitanZero\Services\Assist\AssistRouter;

class TitanZeroActionController extends AccountBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function action(TitanZeroActionRequest $request, AssistRouter $router)
    {
        $payload = $request->validated();
        $result = $router->handle($payload['action'], $payload['context'] ?? [], $payload['input'] ?? '');
        return response()->json($result);
    }
}
