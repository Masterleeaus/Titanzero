<?php

namespace Modules\TitanZero\Http\Controllers\SuperAdmin;

use Illuminate\Routing\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        return view('titanzero::super-admin.dashboard.index');
    }
}
