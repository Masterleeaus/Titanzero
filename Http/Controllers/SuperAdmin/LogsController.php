<?php

namespace Modules\TitanZero\Http\Controllers\SuperAdmin;

use Illuminate\Routing\Controller;

class LogsController extends Controller
{
    public function index()
    {
        return view('titanzero::super-admin.logs.index');
    }
}
