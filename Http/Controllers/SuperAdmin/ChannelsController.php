<?php

namespace Modules\TitanZero\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\TitanZero\Services\Channels\ChannelsService;

class ChannelsController extends Controller
{
    protected ChannelsService $channels;

    public function __construct(ChannelsService $channels)
    {
        $this->channels = $channels;
    }

    public function index()
    {
        $channels = $this->channels->list();

        return view('titanzero::super-admin.channels.index', [
            'channels' => $channels,
        ]);
    }

    public function save(Request $request)
    {
        // Payload shape: channels[key][enabled]=1, channels[key][config][from_number]=...
        $payload = $request->input('channels', []);
        if (!is_array($payload)) {
            $payload = [];
        }

        $this->channels->updateFromRequest($payload);

        return redirect()->back()->with('success', 'Titan Zero channels saved.');
    }

    public function test(string $key)
    {
        // For MVP, this is a config/health test only.
        $health = $this->channels->computeHealth($key);

        return response()->json([
            'ok' => $health['status'] === 'ok',
            'key' => $key,
            'health' => $health,
        ]);
    }
}
