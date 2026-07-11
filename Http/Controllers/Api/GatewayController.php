<?php

namespace Modules\TitanZero\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanZero\Services\ZeroGateway;

class GatewayController extends Controller
{
    public function __construct(protected ZeroGateway $gateway) {}

    public function proposeDocument(Request $request)
    {
        $envelope = $request->all();
        $tenantId = data_get($envelope, 'tenant_id');

        return response()->json(
            $this->gateway->proposeDocument($envelope, $tenantId)
        );
    }

    public function runAgent(Request $request)
    {
        $envelope = $request->all();
        $tenantId = data_get($envelope, 'tenant_id');

        return response()->json(
            $this->gateway->runAgent($envelope, $tenantId)
        );
    }

    public function ingestSignal(Request $request)
    {
        $envelope = $request->all();
        $tenantId = data_get($envelope, 'tenant_id');

        return response()->json(
            $this->gateway->ingestSignal($envelope, $tenantId)
        );
    }
}
