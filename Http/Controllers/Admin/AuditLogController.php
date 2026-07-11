<?php

namespace Modules\TitanZero\Http\Controllers\Admin;

use Illuminate\Routing\Controller;
use Modules\TitanZero\Entities\TitanZeroAuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        $logs = TitanZeroAuditLog::query()->orderByDesc('id')->paginate(50);
        return view('titanzero::admin.audit.index', compact('logs'));
    }
}
